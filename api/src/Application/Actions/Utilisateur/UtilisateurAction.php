<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use App\Application\Actions\Action;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Log\LoggerInterface;

abstract class UtilisateurAction extends Action
{
    public function __construct(LoggerInterface $logger, protected UtilisateurRepository $utilisateurRepository)
    {
        parent::__construct($logger);
    }
}
