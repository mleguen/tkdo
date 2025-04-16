<?php

declare(strict_types=1);

namespace App\Application\Actions\Idee;

use App\Application\Actions\Action;
use App\Domain\Idee\IdeeRepository;
use Psr\Log\LoggerInterface;

abstract class IdeeAction extends Action
{
    public function __construct(LoggerInterface $logger, protected IdeeRepository $ideeRepository)
    {
        parent::__construct($logger);
    }
}
