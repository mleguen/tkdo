<?php
declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\OccasionInconnueException;
use App\Dom\Exception\PasParticipantNiAdminException;
use App\Dom\Port\OccasionPort;
use App\Dom\Repository\OccasionRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class ViewOccasionController extends AuthController
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

    #[\Override]
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $response = parent::__invoke($request, $response, $args);
        $idOccasion = $this->routeService->getIntArg($request, $args, 'idOccasion');

        try {
            $occasion = $this->occasionRepository->read($idOccasion);
            $occasion = $this->occasionPort->getOccasion(
                $this->auth,
                $occasion,
                $resultats
            );
        }
        catch (OccasionInconnueException $err) {
            throw new HttpNotFoundException($request, $err->getMessage());
        }
        catch (PasParticipantNiAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeOccasionDetaillee($occasion, $resultats));
    }
}
