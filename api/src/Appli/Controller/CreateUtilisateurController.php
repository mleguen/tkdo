<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\DomException;
use App\Dom\Exception\EmailInvalideException;
use App\Dom\Exception\GenreInvalideException;
use App\Dom\Exception\IdentifiantDejaUtiliseException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\PrefNotifIdeesInvalideException;
use App\Dom\Port\UtilisateurPort;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

class CreateUtilisateurController extends AuthController
{
    public function __construct(
        private readonly JsonService $jsonService,
        RouteService $routeService,
        private readonly UtilisateurPort $utilisateurPort
    ) {
        parent::__construct($routeService);
    }

    #[\Override]
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $response = parent::__invoke($request, $response, $args);
        $body = $this->routeService->getParsedRequestBody($request, [
            'identifiant',
            'email',
            'nom',
            'genre',
        ]);

        try {
            $utilisateur = $this->utilisateurPort->creeUtilisateur(
                $this->auth,
                $body['identifiant'],
                $body['email'],
                $body['nom'],
                $body['genre'],
                isset($body['admin']) ? boolval($body['admin']) : null,
                $body['prefNotifIdees'] ?? null
            );
        }
        catch (PasAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }
        catch (IdentifiantDejaUtiliseException | EmailInvalideException | PrefNotifIdeesInvalideException | GenreInvalideException $err) {
            throw new HttpBadRequestException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeUtilisateurComplet($utilisateur));
    }
}
