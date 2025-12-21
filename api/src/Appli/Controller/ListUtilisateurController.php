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
    public function __construct(
        private readonly JsonService $jsonService,
        RouteService $routeService,
        private readonly UtilisateurPort $utilisateurPort
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

        try {
            $utilisateurs = $this->utilisateurPort->listeUtilisateurs($this->getAuth());
        }
        catch (PasAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeListeUtilisateurs($utilisateurs));
    }
}
