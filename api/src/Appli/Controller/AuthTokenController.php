<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Appli\ModelAdaptor\AuthCodeAdaptor;
use App\Appli\Service\AuthService;
use App\Appli\Service\RouteService;
use App\Dom\Repository\AuthCodeRepository;
use App\Dom\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use App\Dom\Exception\UtilisateurInconnuException;
use Slim\Exception\HttpUnauthorizedException;

class AuthTokenController
{
    use CookieConfigTrait;

    public function __construct(
        private readonly AuthCodeRepository $authCodeRepository,
        private readonly AuthService $authService,
        private readonly EntityManager $em,
        private readonly LoggerInterface $logger,
        private readonly RouteService $routeService,
        private readonly UtilisateurRepository $utilisateurRepository
    ) {
    }

    /**
     * @param array<string, mixed> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $this->routeService->getParsedRequestBody($request, ['code']);
        $codeClair = $body['code'];

        // Find all auth codes for all users (we don't know which user yet)
        // We need to iterate and check the hash
        $authCode = $this->findValidAuthCode($codeClair);

        if ($authCode === null) {
            throw new HttpUnauthorizedException($request, 'code invalide ou expiré');
        }

        // Atomically mark as used - prevents race conditions
        $marked = $this->authCodeRepository->marqueUtilise($authCode->getId());
        if (!$marked) {
            // Another request already used this code
            throw new HttpUnauthorizedException($request, 'code invalide ou expiré');
        }

        // Load the user (may have been deleted between login and token exchange)
        try {
            $utilisateur = $this->utilisateurRepository->read($authCode->getUtilisateurId());
        } catch (UtilisateurInconnuException) {
            throw new HttpUnauthorizedException($request, 'code invalide ou expiré');
        }

        // Create auth and generate JWT
        // groupe_ids will be populated in Story 2.2+, empty for now
        $auth = AuthAdaptor::fromUtilisateur($utilisateur, []);
        $jwt = $this->authService->encode($auth);

        $this->logger->info("Utilisateur {$utilisateur->getId()} ({$utilisateur->getNom()}) connecté via token exchange" . ($utilisateur->getAdmin() ? ' (admin)' : ''));

        // Set HttpOnly cookie with JWT via response header
        $response = $this->addCookieHeader($response, $jwt);

        // Return user payload (without the JWT)
        return $this->routeService->getResponseWithJsonBody($response, json_encode([
            'utilisateur' => [
                'id' => $utilisateur->getId(),
                'nom' => $utilisateur->getNom(),
                'email' => $utilisateur->getEmail(),
                'genre' => $utilisateur->getGenre(),
                'admin' => $utilisateur->getAdmin(),
                'groupe_ids' => [], // Will be populated in Story 2.2+
            ],
        ], JSON_THROW_ON_ERROR));
    }

    private function findValidAuthCode(string $codeClair): ?AuthCodeAdaptor
    {
        // Query all non-expired, non-used auth codes and check the hash
        // This is necessary because we store hashed codes
        $qb = $this->em->createQueryBuilder();
        $qb->select('c')
            ->from(AuthCodeAdaptor::class, 'c')
            ->where('c.expiresAt > :now')
            ->andWhere('c.usedAt IS NULL')
            ->setParameter('now', new \DateTime());

        /** @var AuthCodeAdaptor[] $codes */
        $codes = $qb->getQuery()->getResult();

        foreach ($codes as $code) {
            if ($code->verifieCode($codeClair)) {
                return $code;
            }
        }

        return null;
    }

    private function addCookieHeader(ResponseInterface $response, string $jwt): ResponseInterface
    {
        $expires = time() + $this->authService->getValidite();
        $expiresGmt = gmdate('D, d M Y H:i:s', $expires) . ' GMT';

        $cookie = sprintf(
            'tkdo_jwt=%s; Expires=%s; Path=%s; %sHttpOnly; SameSite=Strict',
            $jwt,
            $expiresGmt,
            $this->getCookiePath(),
            $this->getSecureFlag()
        );

        return $response->withHeader('Set-Cookie', $cookie);
    }
}
