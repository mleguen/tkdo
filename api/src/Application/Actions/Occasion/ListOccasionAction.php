<?php
declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Actions\Action;
use App\Application\Serializable\Occasion\SerializableOccasion;
use App\Domain\Occasion\OccasionRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class ListOccasionAction extends Action
{
    protected $occasionRepository;

    public function __construct(LoggerInterface $logger, OccasionRepository $occasionRepository)
    {
        parent::__construct($logger);
        $this->occasionRepository = $occasionRepository;
    }

    protected function action(): Response
    {
        $this->assertAuth();

        $queryParams = $this->request->getQueryParams();
        if (!isset($queryParams['idParticipant'])) throw new HttpBadRequestException($this->request, 'idParticipant manquant');
        $idParticipant = (int) $queryParams['idParticipant'];
        $this->assertUtilisateurAuthEst([$idParticipant]);

        $occasions = $this->occasionRepository->readByParticipant($idParticipant);
        return $this->respondWithData(array_map(function ($occasion) {
            return new SerializableOccasion($occasion);
        }, $occasions));
    }
}
