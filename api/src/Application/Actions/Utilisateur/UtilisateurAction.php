<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use App\Application\Actions\Action;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpForbiddenException;

class UtilisateurAction extends Action
{
  /**
   * @var UtilisateurRepository
   */
  protected $utilisateurRepository;

  /**
   * @var int
   */
  protected $idUtilisateur;

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
    $this->idUtilisateur = (int) $this->resolveArg('idUtilisateur');

    $idUtilisateurAuth = $this->request->getAttribute('idUtilisateurAuth');
    if ($this->idUtilisateur !== $idUtilisateurAuth) {
      $this->logger->warning("L'utilisateur authentifié ($idUtilisateurAuth) n'est pas l'utilisateur ($this->idUtilisateur)");
      throw new HttpForbiddenException($this->request);
    }

    return $this->response;
  }
}
