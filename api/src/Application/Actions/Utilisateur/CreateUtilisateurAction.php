<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use App\Application\Serializable\Utilisateur\SerializableUtilisateur;
use App\Application\Service\PasswordService;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class CreateUtilisateurAction extends UtilisateurAction
{
  private $passwordService;

  public function __construct(
    LoggerInterface $logger,
    UtilisateurRepository $utilisateurRepository,
    PasswordService $passwordService
  ) {
    parent::__construct($logger, $utilisateurRepository);
    $this->passwordService = $passwordService;
  }

  protected function action(): Response
  {
    parent::action();
    $this->assertUtilisateurAuthEstAdmin();
    $body = $this->getFormData();

    $mdp = $this->passwordService->randomPassword();
    $utilisateur = $this->utilisateurRepository->create(
      $body['identifiant'],
      password_hash($mdp, PASSWORD_DEFAULT),
      $body['nom'],
      $body['genre'],
      boolval($body['estAdmin'])
    );

    return $this->respondWithData(new SerializableUtilisateur($utilisateur, true, $mdp));
  }
}
