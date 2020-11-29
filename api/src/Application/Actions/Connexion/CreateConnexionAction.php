<?php

declare(strict_types=1);

namespace App\Application\Actions\Connexion;

use App\Application\Actions\Action;
use App\Application\Service\AuthService;
use App\Application\Service\MailerService;
use App\Domain\Utilisateur\UtilisateurNotFoundException;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class CreateConnexionAction extends Action
{
  protected $utilisateurRepository;
  protected $authService;

  public function __construct(
    LoggerInterface $logger,
    UtilisateurRepository $utilisateurRepository,
    AuthService $authService
  )
  {
    parent::__construct($logger);
    $this->utilisateurRepository = $utilisateurRepository;
    $this->authService = $authService;
  }

  protected function action(): Response
  {
    $body = $this->getFormData();
    try {
      $utilisateur = $this->utilisateurRepository->readOneByIdentifiant($body['identifiant']);
      if (!password_verify($body['mdp'], $utilisateur->getMdp())) {
        throw new UtilisateurNotFoundException();
      }
      
      $id = $utilisateur->getId();
      $nom = $utilisateur->getNom();
      $estAdmin = $utilisateur->getEstAdmin();
      $this->logger->info("Utilisateur $id ($nom) connecté" . ($estAdmin ? " (admin)" : ""));

      return $this->respondWithData([
        "token" => $this->authService->encodeAuthToken($utilisateur->getId(), $estAdmin),
        "utilisateur" => [
          "id" => $id,
          "nom" => $nom,
          "estAdmin" => $estAdmin,
        ]
      ]);
    }
    catch (UtilisateurNotFoundException $err) {
      throw new HttpBadRequestException($this->request, "identifiants invalides");
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      throw $e;
    }
  }
}
