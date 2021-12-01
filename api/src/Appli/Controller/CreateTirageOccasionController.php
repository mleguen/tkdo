<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\OccasionInconnueException;
use App\Dom\Exception\OccasionPasseeException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\TirageDejaLanceException;
use App\Dom\Exception\TirageEchoueException;
use App\Dom\Port\OccasionPort;
use App\Dom\Repository\OccasionRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;

class CreateTirageOccasionController extends AuthController
{
    protected $jsonService;
    protected $occasionPort;
    protected $occasionRepository;

    public function __construct(
        JsonService $jsonService,
        OccasionPort $occasionPort,
        OccasionRepository $occasionRepository,
        RouteService $routeService
    ) {
        parent::__construct($routeService);
        $this->jsonService = $jsonService;
        $this->occasionPort = $occasionPort;
        $this->occasionRepository = $occasionRepository;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $response = parent::__invoke($request, $response, $args);
        $idOccasion = $this->routeService->getIntArg($request, $args, 'idOccasion');
        $body = $this->routeService->getParsedRequestBody($request);

        try {
            $occasion = $this->occasionRepository->read($idOccasion);
            $force = isset($body['force']) && boolval($body['force']);
            $nbMaxIter = isset($body['nbMaxIter']) ? intval($body['nbMaxIter']) : null;
            $resultats = $this->occasionPort->lanceTirage(
                $this->auth,
                $occasion,
                $force,
                $nbMaxIter
            );
        } catch (OccasionInconnueException $err) {
            throw new HttpNotFoundException($request, $err->getMessage());
        } catch (OccasionPasseeException | TirageDejaLanceException $err) {
            throw new HttpBadRequestException($request, $err->getMessage());
        } catch (PasAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        } catch (TirageEchoueException $err) {
            throw new HttpInternalServerErrorException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeOccasionDetaillee($occasion, $resultats));
    }
}
