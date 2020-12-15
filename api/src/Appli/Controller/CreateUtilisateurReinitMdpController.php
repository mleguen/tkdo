<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Port\UtilisateurPort;
use App\Dom\Repository\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class CreateUtilisateurReinitMdpController extends AuthController
{
    private $jsonService;
    private $utilisateurPort;
    private $utilisateurRepository;

    public function __construct(
        JsonService $jsonService,
        RouteService $routeService,
        UtilisateurPort $utilisateurPort,
        UtilisateurRepository $utilisateurRepository
    )
    {
        parent::__construct($routeService);
        $this->jsonService = $jsonService;
        $this->utilisateurPort = $utilisateurPort;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $response = parent::__invoke($request, $response, $args);
        $idUtilisateur = $this->routeService->getIntArg($request, $args, 'idUtilisateur');

        try {
            $utilisateur = $this->utilisateurPort->reinitMdpUtilisateur(
                $this->auth,
                $this->utilisateurRepository->read($idUtilisateur)
            );
        }
        catch (UtilisateurInconnuException $err) {
            throw new HttpNotFoundException($request, $err->getMessage());
        }
        catch (PasAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeUtilisateurComplet($utilisateur));
    }
}
