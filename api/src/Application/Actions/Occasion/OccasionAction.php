<?php
declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Actions\Action;
use App\Domain\Occasion\OccasionRepository;
use Psr\Log\LoggerInterface;

abstract class OccasionAction extends Action
{
    /**
     * @var OccasionRepository
     */
    protected $occasionRepository;

    /**
     * @param LoggerInterface $logger
     * @param OccasionRepository  $occasionRepository
     */
    public function __construct(LoggerInterface $logger, OccasionRepository $occasionRepository)
    {
        parent::__construct($logger);
        $this->occasionRepository = $occasionRepository;
    }
}
