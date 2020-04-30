<?php

declare(strict_types=1);

namespace App\Application\Actions\Connexion;

use App\Application\Actions\Action;
use App\Application\Mock\MockData;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class PostConnexionAction extends Action
{
  /**
   * @var MockData
   */
  protected $mock;

  /**
   * @var UtilisateurRepository
   */
  protected $utilisateurRepository;

  /**
   * @param LoggerInterface $logger
   * @param MockData  $mock
   * @param UtilisateurRepository  $utilisateurRepository
   */
  public function __construct(LoggerInterface $logger, MockData $mock, UtilisateurRepository $utilisateurRepository)
  {
    parent::__construct($logger);
    $this->mock = $mock;
    $this->utilisateurRepository = $utilisateurRepository;
  }

  protected function action(): Response
  {
    $body = $this->getFormData();
    try {
      $utilisateur = $this->utilisateurRepository->findByIdentifiants($body->identifiant, $body->mdp);
      $nom = $utilisateur->getNom();
      $this->logger->info("$nom connecté(e)");

      // TODO: construire le JWT
      return $this->respondWithData([
        "idUtilisateur" => $utilisateur->getId(),
        "token" => $this->mock->getToken()
      ]);
    }
    catch (UtilisateurInconnuException $err) {
      throw new HttpBadRequestException($this->request, "identifiants invalides");
    }
  }
}
