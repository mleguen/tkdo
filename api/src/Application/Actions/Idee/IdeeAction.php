<?php

declare(strict_types=1);

namespace App\Application\Actions\Idee;

use App\Application\Actions\Utilisateur\UtilisateurAction;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Log\LoggerInterface;

class IdeeAction extends UtilisateurAction
{
  /**
   * @var IdeeRepository
   */
  protected $ideeRepository;

  /**
   * @param LoggerInterface $logger
   * @param UtilisateurRepository  $utilisateurRepository
   * @param IdeeRepository  $ideeRepository
   */
  public function __construct(LoggerInterface $logger, UtilisateurRepository $utilisateurRepository, IdeeRepository $ideeRepository)
  {
    parent::__construct($logger, $utilisateurRepository);
    $this->ideeRepository = $ideeRepository;
  }
}
