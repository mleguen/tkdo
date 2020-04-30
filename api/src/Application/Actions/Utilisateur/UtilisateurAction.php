<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use App\Application\Actions\Action;
use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class UtilisateurAction extends Action
{
  /**
   * @var UtilisateurRepository
   */
  protected $utilisateurRepository;

  /**
   * @var Utilisateur
   */
  protected $utilisateur;

  /**
   * @param LoggerInterface $logger
   * @param UtilisateurRepository  $utilisateurRepository
   */
  public function __construct(LoggerInterface $logger, UtilisateurRepository $utilisateurRepository)
  {
    parent::__construct($logger);
    $this->utilisateurRepository = $utilisateurRepository;
  }

  protected function action(): Response
  {
    $this->utilisateur = $this->utilisateurRepository->find((int) $this->resolveArg('idUtilisateur'));
    return $this->response;
  }
}
