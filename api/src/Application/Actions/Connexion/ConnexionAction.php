<?php

declare(strict_types=1);

namespace App\Application\Actions\Connexion;

use App\Application\Actions\Action;
use App\Application\Service\AuthService;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Domain\Utilisateur\UtilisateurRepository;
use Exception;
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
   * @var AuthService
   */
  protected $authService;

  /**
   * @param LoggerInterface $logger
   * @param UtilisateurRepository  $utilisateurRepository
   */
  public function __construct(LoggerInterface $logger, UtilisateurRepository $utilisateurRepository, AuthService $authService)
  {
    parent::__construct($logger);
    $this->utilisateurRepository = $utilisateurRepository;
    $this->authService = $authService;
  }

  protected function action(): Response
  {
    $body = $this->getFormData();
    try {
      $utilisateur = $this->utilisateurRepository->readOneByIdentifiants($body->identifiant, $body->mdp);
      $nom = $utilisateur->getNom();
      $this->logger->info("$nom connecté(e)");

      return $this->respondWithData([
        "token" => $this->authService->encode($utilisateur->getId()),
        "utilisateur" => [
          "id" => $utilisateur->getId(),
          "nom" => $utilisateur->getNom(),
        ]
      ]);
    }
    catch (UtilisateurInconnuException $err) {
      throw new HttpBadRequestException($this->request, "identifiants invalides");
    }
    catch (Exception $e) {
      $this->logger->error($e->getMessage());
      throw $e;
    }
  }
}
