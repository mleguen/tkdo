<?php
declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Port\GroupePort;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class ListGroupeController extends AuthController
{
    public function __construct(
        private readonly GroupePort $groupePort,
        private readonly JsonService $jsonService,
        private readonly LoggerInterface $logger,
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

        $groupes = $this->groupePort->listeGroupesUtilisateur($this->getAuth());

        if (empty($groupes['actifs']) && empty($groupes['archives'])) {
            $this->logger->debug('User has zero groups', [
                'utilisateurId' => $this->getAuth()->getIdUtilisateur(),
            ]);
        }

        return $this->routeService->getResponseWithJsonBody(
            $response,
            $this->jsonService->encodeListeGroupes(
                $groupes['actifs'],
                $groupes['archives'],
                $this->getAuth()->getGroupeAdminIds()
            )
        );
    }
}
