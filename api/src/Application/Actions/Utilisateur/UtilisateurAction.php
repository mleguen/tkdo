<?php
declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use App\Application\Actions\Action;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Log\LoggerInterface;

abstract class UtilisateurAction extends Action
{
    /**
     * @var UtilisateurRepository
     */
    protected $utilisateurRepository;

    /**
     * @param LoggerInterface $logger
     * @param UtilisateurRepository  $utilisateurRepository
     */
    public function __construct(LoggerInterface $logger, UtilisateurRepository $utilisateurRepository)
    {
        parent::__construct($logger);
        $this->utilisateurRepository = $utilisateurRepository;
    }
}
