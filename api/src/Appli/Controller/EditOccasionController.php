<?php
declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\DateService;
use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\OccasionInconnueException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Port\OccasionPort;
use App\Dom\Repository\OccasionRepository;
use App\Infra\Tools\ArrayTools;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class EditOccasionController extends AuthController
{
    protected $dateService;
    protected $occasionPort;
    protected $occasionRepository;
    protected $jsonService;

    public function __construct(
        DateService $dateService,
        JsonService $jsonService,
        OccasionPort $occasionPort,
        OccasionRepository $occasionRepository,
        RouteService $routeService
    ) {
        parent::__construct($routeService);
        $this->dateService = $dateService;
        $this->jsonService = $jsonService;
        $this->occasionPort = $occasionPort;
        $this->occasionRepository = $occasionRepository;
    }

    #[\Override]
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $response = parent::__invoke($request, $response, $args);
        $idOccasion = $this->routeService->getIntArg($request, $args, 'idOccasion');
        $body = $this->routeService->getParsedRequestBody($request);

        $modifications = ArrayTools::pick($body, ['titre']);
        if (isset($body['date'])) $modifications['date'] = $this->dateService->decodeDate($body['date']);

        try {
            $occasion = $this->occasionPort->modifieOccasion(
                $this->auth,
                $this->occasionRepository->read($idOccasion),
                $modifications
            );
        }
        catch (OccasionInconnueException $err) {
            throw new HttpNotFoundException($request, $err->getMessage());
        }
        catch (PasAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeOccasion($occasion));
    }
}
