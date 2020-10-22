<?php

declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Actions\Action;
use App\Application\Serializable\Occasion\SerializableOccasion;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\ResultatTirage\ResultatTirageRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class OccasionReadAction extends Action
{
  /**
   * @var OccasionRepository
   */
  protected $occasionRepository;

  /**
   * @var ResultatTirageRepository
   */
  protected $resultatTirageRepository;

  /**
   * @param LoggerInterface     $logger
   * @param OccasionRepository  $occasionRepository
   */
  public function __construct(LoggerInterface $logger, OccasionRepository $occasionRepository, ResultatTirageRepository $resultatTirageRepository)
  {
    parent::__construct($logger);
    $this->occasionRepository = $occasionRepository;
    $this->resultatTirageRepository = $resultatTirageRepository;
  }

  protected function action(): Response
  {
    $this->assertAuth();
    $occasion = $this->occasionRepository->readLast(); 
    return $this->respondWithData(new SerializableOccasion(
      $occasion,
      $this->resultatTirageRepository->readByOccasion($occasion)
    ));
  }
}
