<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\OccasionInconnueException;
use App\Dom\Exception\OccasionPasseeException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\PasParticipantException;
use App\Dom\Exception\TiragePasEncoreLanceException;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Port\OccasionPort;
use App\Dom\Repository\OccasionRepository;
use App\Dom\Repository\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class ResendCreateTirageOccasionController extends AuthController
{
    public function __construct(
        protected JsonService $jsonService,
        protected OccasionPort $occasionPort,
        protected OccasionRepository $occasionRepository,
        protected UtilisateurRepository $utilisateurRepository,
        RouteService $routeService
    ) {
        parent::__construct($routeService);
    }

    #[\Override]
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $response = parent::__invoke($request, $response, $args);
        $idOccasion = $this->routeService->getIntArg($request, $args, 'idOccasion');
        $body = $this->routeService->getParsedRequestBody($request);

        try {
            $occasion = $this->occasionRepository->read($idOccasion);
            $participant = $this->utilisateurRepository->read(intval($body['idParticipant']));
            $this->occasionPort->renvoieEmailLancementTirage(
                $this->auth,
                $occasion,
                $participant
            );
        } catch (OccasionInconnueException $err) {
            throw new HttpNotFoundException($request, $err->getMessage());
        } catch (OccasionPasseeException | UtilisateurInconnuException | PasParticipantException | TiragePasEncoreLanceException $err) {
            throw new HttpBadRequestException($request, $err->getMessage());
        } catch (PasAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeParticipant($occasion, $participant));
    }
}
