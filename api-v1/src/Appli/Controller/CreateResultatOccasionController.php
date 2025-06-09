<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\OccasionInconnueException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\PasParticipantNiAdminException;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Exception\UtilisateurOffreOuRecoitDejaException;
use App\Dom\Port\OccasionPort;
use App\Dom\Repository\OccasionRepository;
use App\Dom\Repository\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class CreateResultatOccasionController extends AuthController
{
    protected $jsonService;
    protected $occasionPort;
    protected $occasionRepository;
    protected $utilisateurRepository;

    public function __construct(
        JsonService $jsonService,
        OccasionPort $occasionPort,
        OccasionRepository $occasionRepository,
        UtilisateurRepository $utilisateurRepository,
        RouteService $routeService
    ) {
        parent::__construct($routeService);
        $this->jsonService = $jsonService;
        $this->occasionPort = $occasionPort;
        $this->occasionRepository = $occasionRepository;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    #[\Override]
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $response = parent::__invoke($request, $response, $args);
        $idOccasion = $this->routeService->getIntArg($request, $args, 'idOccasion');
        $body = $this->routeService->getParsedRequestBody($request, ['idQuiOffre', 'idQuiRecoit']);

        try {
            $occasion = $this->occasionRepository->read($idOccasion);
            $quiOffre = $this->utilisateurRepository->read(intval($body['idQuiOffre']));
            $quiRecoit = $this->utilisateurRepository->read(intval($body['idQuiRecoit']));
            $resultat = $this->occasionPort->ajouteResultatOccasion(
                $this->auth,
                $occasion,
                $quiOffre,
                $quiRecoit
            );
        }
        catch (OccasionInconnueException $err) {
            throw new HttpNotFoundException($request, $err->getMessage());
        }
        catch (UtilisateurInconnuException | UtilisateurOffreOuRecoitDejaException $err) {
            throw new HttpBadRequestException($request, $err->getMessage());
        }
        catch (PasAdminException | PasParticipantNiAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeResultat($resultat));
    }
}
