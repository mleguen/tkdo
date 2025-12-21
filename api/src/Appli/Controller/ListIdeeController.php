<?php
declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Repository\UtilisateurRepository;
use App\Dom\Port\IdeePort;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class ListIdeeController extends AuthController
{
    public function __construct(
        private readonly IdeePort $ideePort,
        private readonly JsonService $jsonService,
        RouteService $routeService,
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

        $queryParams = $request->getQueryParams();
        if (!isset($queryParams['idUtilisateur'])) throw new HttpBadRequestException($request, 'idUtilisateur manquant');
       
        try {
            $utilisateur = $this->utilisateurRepository->read((int) $queryParams['idUtilisateur']);
            $idees = $this->ideePort->listeIdees(
                $this->getAuth(),
                $utilisateur,
                isset($queryParams['supprimees']) ? boolval($queryParams['supprimees']) : null
            );
        }
        catch (UtilisateurInconnuException $err) {
            throw new HttpNotFoundException($request, $err->getMessage());
        }
        catch (PasAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeListeIdees($utilisateur, $idees));
    }
}
