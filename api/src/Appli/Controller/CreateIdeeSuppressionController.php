<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\IdeeDejaSupprimeeException;
use App\Dom\Exception\IdeeInconnueException;
use App\Dom\Exception\IdeePasAuteurException;
use App\Dom\Repository\IdeeRepository;
use App\Dom\Port\IdeePort;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class CreateIdeeSuppressionController extends AuthController
{
    protected $ideePort;
    protected $ideeRepository;
    protected $jsonService;

    public function __construct(
        IdeePort $ideePort,
        IdeeRepository $ideeRepository,
        JsonService $jsonService,
        RouteService $routeService
    ) {
        parent::__construct($routeService);
        $this->ideePort = $ideePort;
        $this->ideeRepository = $ideeRepository;
        $this->jsonService = $jsonService;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $response = parent::__invoke($request, $response, $args);
        $idIdee = $this->routeService->getIntArg($request, $args, 'idIdee');

        try {
            $idee = $this->ideePort->marqueIdeeCommeSupprimee(
                $this->auth,
                $this->ideeRepository->read($idIdee)
            );
        }
        catch (IdeeInconnueException $err) {
            throw new HttpNotFoundException($request, $err->getMessage());
        }
        catch (IdeePasAuteurException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }
        catch (IdeeDejaSupprimeeException $err) {
            throw new HttpBadRequestException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeIdee($idee));
    }
}
