<?php

declare(strict_types=1);

namespace App\Application\Actions\Resultat;

use App\Application\Actions\Action;
use App\Domain\Resultat\ResultatRepository;
use Psr\Log\LoggerInterface;

abstract class ResultatAction extends Action
{
    public function __construct(LoggerInterface $logger, protected ResultatRepository $resultatRepository)
    {
        parent::__construct($logger);
    }
}
