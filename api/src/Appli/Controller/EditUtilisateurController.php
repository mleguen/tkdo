<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\DomException;
use App\Dom\Exception\EmailInvalideException;
use App\Dom\Exception\GenreInvalideException;
use App\Dom\Exception\IdentifiantDejaUtiliseException;
use App\Dom\Exception\ModificationMdpInterditeException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\PasUtilisateurNiAdminException;
use App\Dom\Exception\PrefNotifIdeesInvalideException;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Port\UtilisateurPort;
use App\Dom\Repository\UtilisateurRepository;
use App\Infra\Tools\ArrayTools;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class EditUtilisateurController extends AuthController
{
    public function __construct(
        private readonly JsonService $jsonService,
        RouteService $routeService,
        private readonly UtilisateurPort $utilisateurPort,
        private readonly UtilisateurRepository $utilisateurRepository
    ) {
        parent::__construct($routeService);
    }

    /**
     * @param array<string, mixed> $args
     */
    #[\Override]
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response = parent::__invoke($request, $response, $args);
        $idUtilisateur = $this->routeService->getIntArg($request, $args, 'idUtilisateur');
        $body = $this->routeService->getParsedRequestBody($request);

        $modifications = ArrayTools::pick($body, [
            'email',
            'genre',
            'identifiant',
            'mdp',
            'nom',
            'prefNotifIdees',
        ]);
        if (isset($body['admin'])) $modifications['admin'] = boolval($body['admin']);

        try {
            $utilisateur = $this->utilisateurPort->modifieUtilisateur(
                $this->getAuth(),
                $this->utilisateurRepository->read($idUtilisateur),
                $modifications
            );
        }
        catch (UtilisateurInconnuException $err) {
            throw new HttpNotFoundException($request, $err->getMessage());
        }
        catch (PasUtilisateurNiAdminException | ModificationMdpInterditeException | PasAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }
        catch (PrefNotifIdeesInvalideException | EmailInvalideException | IdentifiantDejaUtiliseException | GenreInvalideException $err) {
            throw new HttpBadRequestException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeUtilisateurComplet($utilisateur));
    }
}
