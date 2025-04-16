<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\DateService;
use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\PasAdminException;
use App\Dom\Port\OccasionPort;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

class CreateOccasionController extends AuthController
{
    protected $dateService;
    protected $occasionPort;
    protected $jsonService;

    public function __construct(
        DateService $dateService,
        JsonService $jsonService,
        OccasionPort $occasionPort,
        RouteService $routeService
    ) {
        parent::__construct($routeService);
        $this->dateService = $dateService;
        $this->jsonService = $jsonService;
        $this->occasionPort = $occasionPort;
    }

    #[\Override]
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $response = parent::__invoke($request, $response, $args);
        $body = $this->routeService->getParsedRequestBody($request, ['date', 'titre']);

        try {
            $occasion = $this->occasionPort->creeOccasion(
                $this->auth,
                $this->dateService->decodeDate($body['date']),
                $body['titre']
            );
        }
        catch (PasAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeOccasion($occasion));
    }
}
