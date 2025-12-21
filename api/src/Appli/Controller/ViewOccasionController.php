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
    public function __construct(
        private readonly JsonService $jsonService,
        private readonly OccasionPort $occasionPort,
        private readonly OccasionRepository $occasionRepository,
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
        $idOccasion = $this->routeService->getIntArg($request, $args, 'idOccasion');

        try {
            $occasion = $this->occasionRepository->read($idOccasion);
            $resultats = [];
            $occasion = $this->occasionPort->getOccasion(
                $this->getAuth(),
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
