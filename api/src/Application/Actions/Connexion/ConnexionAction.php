<?php

declare(strict_types=1);

namespace App\Application\Actions\Connexion;

use App\Application\Actions\Action;
use App\Application\Service\TokenService;
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
   * @var TokenService
   */
  protected $tokenService;

  /**
   * @param LoggerInterface $logger
   * @param UtilisateurRepository  $utilisateurRepository
   */
  public function __construct(LoggerInterface $logger, UtilisateurRepository $utilisateurRepository, TokenService $tokenService)
  {
    parent::__construct($logger);
    $this->utilisateurRepository = $utilisateurRepository;
    $this->tokenService = $tokenService;
  }

  protected function action(): Response
  {
    $body = $this->getFormData();
    try {
      $utilisateur = $this->utilisateurRepository->readOneByIdentifiants($body->identifiant, $body->mdp);
      $nom = $utilisateur->getNom();
      $this->logger->info("$nom connecté(e)");

      return $this->respondWithData([
        "token" => $this->tokenService->encode($utilisateur->getId()),
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
