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
    // TODO: obtenir une référence à la place pour éviter une lecture en base
    // et faire la vraie lecture plus tard dans les classes filles qui le nécessitent
    $this->utilisateur = $this->utilisateurRepository->read((int) $this->resolveArg('idUtilisateur'));
    return $this->response;
  }
}
