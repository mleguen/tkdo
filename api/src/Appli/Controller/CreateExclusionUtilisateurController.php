<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\DoublonExclusionException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Port\ExclusionPort;
use App\Dom\Repository\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class CreateExclusionUtilisateurController extends AuthController
{
    protected $jsonService;
    protected $exclusionPort;
    protected $utilisateurRepository;

    public function __construct(
        JsonService $jsonService,
        RouteService $routeService,
        ExclusionPort $exclusionPort,
        UtilisateurRepository $utilisateurRepository
    ) {
        parent::__construct($routeService);
        $this->jsonService = $jsonService;
        $this->exclusionPort = $exclusionPort;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $response = parent::__invoke($request, $response, $args);

        try {
            $quiOffre = $this->utilisateurRepository->read($this->routeService->getIntArg($request, $args, 'idUtilisateur'));
        } catch (UtilisateurInconnuException $err) {
            throw new HttpNotFoundException($request, $err->getMessage());
        }

        $body = $this->routeService->getParsedRequestBody($request, ['idQuiNeDoitPasRecevoir']);

        try {
            $quiNeDoitPasRecevoir = $this->utilisateurRepository->read(intval($body['idQuiNeDoitPasRecevoir']));
            $exclusion = $this->exclusionPort->creeExclusion(
                $this->auth,
                $quiOffre,
                $quiNeDoitPasRecevoir
            );
        }
        catch (UtilisateurInconnuException | DoublonExclusionException $err) {
            throw new HttpBadRequestException($request, $err->getMessage());
        }
        catch (PasAdminException $err) {
            throw new HttpForbiddenException($request, $err->getMessage());
        }

        return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeExclusion($exclusion));
    }
}
