<?php

declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Actions\Action;
use App\Application\Service\DateService;
use App\Domain\Occasion\OccasionRepository;
use Psr\Log\LoggerInterface;

abstract class OccasionAction extends Action
{
  protected $occasionRepository;
  protected $dateService;
  
  public function __construct(
    LoggerInterface $logger,
    OccasionRepository $occasionRepository,
    DateService $dateService
  ) {
    parent::__construct($logger);
    $this->occasionRepository = $occasionRepository;
    $this->dateService = $dateService;
  }
}
