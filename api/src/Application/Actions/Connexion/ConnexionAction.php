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

class ConnexionAction extends Action
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

  protected function action(): Response
  {
    $body = $this->getFormData();
    try {
      $utilisateur = $this->utilisateurRepository->readOneByIdentifiants($body->identifiant, $body->mdp);
      $nom = $utilisateur->getNom();
      $this->logger->info("$nom connecté(e)");

      // TODO: construire le JWT
      return $this->respondWithData([
        "idUtilisateur" => $utilisateur->getId(),
        "token" => MockData::getToken()
      ]);
    }
    catch (UtilisateurInconnuException $err) {
      throw new HttpBadRequestException($this->request, "identifiants invalides");
    }
  }
}
