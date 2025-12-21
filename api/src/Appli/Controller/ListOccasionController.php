<?php
declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\DomException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\PasUtilisateurNiAdminException;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Port\OccasionPort;
use App\Dom\Repository\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class ListOccasionController extends AuthController
{
    public function __construct(
        private readonly JsonService $jsonService,
        private readonly OccasionPort $occasionPort,
        private readonly UtilisateurRepository $utilisateurRepository,
        RouteService $routeService
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
        
        if (isset($queryParams['idParticipant'])) {
            try {
                $participant = $this->utilisateurRepository->read((int) $queryParams['idParticipant']);
                $occasions = $this->occasionPort->listeOccasionsParticipant(
                    $this->getAuth(),
                    $participant
                );
            }
            catch (UtilisateurInconnuException $err) {
                throw new HttpNotFoundException($request, $err->getMessage());
            }
            catch (PasUtilisateurNiAdminException $err) {
                throw new HttpForbiddenException($request, $err->getMessage());
            }
        } else {
            try {
                $occasions = $this->occasionPort->listeOccasions($this->getAuth());
            }
            catch (PasAdminException $err) {
                throw new HttpForbiddenException($request, $err->getMessage());
            }
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeListeOccasions($occasions));
    }
}
