<?php
declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Actions\Action;
use App\Application\Serializable\Occasion\SerializableOccasion;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\Resultat\ResultatRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ViewOccasionAction extends Action
{
    /**
     * @var OccasionRepository
     */
    protected $occasionRepository;

    /**
     * @var ResultatRepository
     */
    protected $resultatRepository;

    /**
     * @param LoggerInterface     $logger
     * @param OccasionRepository  $occasionRepository
     */
    public function __construct(LoggerInterface $logger, OccasionRepository $occasionRepository, ResultatRepository $resultatRepository)
    {
        parent::__construct($logger);
        $this->occasionRepository = $occasionRepository;
        $this->resultatRepository = $resultatRepository;
    }

    protected function action(): Response
    {
        $this->assertAuth();
        $occasion = $this->occasionRepository->readLast();
        return $this->respondWithData(new SerializableOccasion(
            $occasion,
            $this->resultatRepository->readByOccasion($occasion)
        ));
    }
}
