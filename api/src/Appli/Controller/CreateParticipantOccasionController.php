<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\OccasionInconnueException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\UtilisateurDejaParticipantException;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Port\OccasionPort;
use App\Dom\Repository\OccasionRepository;
use App\Dom\Repository\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class CreateParticipantOccasionController extends AuthController
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
        $body = $this->routeService->getParsedRequestBody($request, ['idParticipant']);

        try {
            $occasion = $this->occasionRepository->read($idOccasion);
            $participant = $this->utilisateurRepository->read(intval($body['idParticipant']));
            $occasion = $this->occasionPort->ajouteParticipantOccasion(
                $this->auth,
                $occasion,
                $participant
            );
        }
        catch (OccasionInconnueException $err) {
            throw new HttpNotFoundException($request, $err->getMessage());
        }
        catch (UtilisateurInconnuException | UtilisateurDejaParticipantException $err) {
            throw new HttpBadRequestException($request, $err->getMessage());
        }
        catch (PasAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeParticipant($occasion, $participant));
    }
}
