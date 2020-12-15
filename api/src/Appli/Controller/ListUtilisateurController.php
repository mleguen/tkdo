<?php
declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\PasAdminException;
use App\Dom\Port\UtilisateurPort;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

class ListUtilisateurController extends AuthController
{
    private $jsonService;
    private $utilisateurPort;

    public function __construct(
        JsonService $jsonService,
        RouteService $routeService,
        UtilisateurPort $utilisateurPort
    ) {
        parent::__construct($routeService);
        $this->jsonService = $jsonService;
        $this->utilisateurPort = $utilisateurPort;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $response = parent::__invoke($request, $response, $args);

        try {
            $utilisateurs = $this->utilisateurPort->listeUtilisateurs($this->auth);
        }
        catch (PasAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeListeUtilisateurs($utilisateurs));
    }
}
