<?php

declare(strict_types=1);

namespace App\Application\Actions\Connexion;

use App\Application\Actions\Action;
use App\Domain\Connexion\ConnexionRepository;
use Psr\Log\LoggerInterface;

abstract class ConnexionAction extends Action
{
    protected ConnexionRepository $connexionRepository;

    public function __construct(LoggerInterface $logger, ConnexionRepository $connexionRepository)
    {
        parent::__construct($logger);
        $this->connexionRepository = $connexionRepository;
    }
}
