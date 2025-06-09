<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\IdeePasAuteurException;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Repository\UtilisateurRepository;
use App\Dom\Port\IdeePort;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

class CreateIdeeController extends AuthController
{
    protected $ideePort;
    protected $jsonService;
    protected $utilisateurRepository;

    public function __construct(
        IdeePort $ideePort,
        JsonService $jsonService,
        RouteService $routeService,
        UtilisateurRepository $utilisateurRepository
    ) {
        parent::__construct($routeService);
        $this->ideePort = $ideePort;
        $this->jsonService = $jsonService;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    #[\Override]
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $response = parent::__invoke($request, $response, $args);
        $body = $this->routeService->getParsedRequestBody($request, ['idUtilisateur', 'description', 'idAuteur']);

        try {
            $idee = $this->ideePort->creeIdee(
                $this->auth,
                $this->utilisateurRepository->read(intval($body['idUtilisateur'])),
                $body['description'],
                $this->utilisateurRepository->read(intval($body['idAuteur']), true)
            );
        }
        catch (UtilisateurInconnuException $err) {
            throw new HttpBadRequestException($request, $err->getMessage());
        }
        catch (IdeePasAuteurException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }
        
        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeIdee($idee));
    }
}
