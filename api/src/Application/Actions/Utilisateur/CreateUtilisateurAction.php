<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use App\Application\Serializable\Utilisateur\SerializableUtilisateur;
use App\Application\Service\MailerService;
use App\Application\Service\PasswordService;
use App\Domain\Utilisateur\PrefNotifIdees;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class CreateUtilisateurAction extends UtilisateurAction
{
  private $passwordService;
  private $mailerService;

  public function __construct(
    LoggerInterface $logger,
    UtilisateurRepository $utilisateurRepository,
    PasswordService $passwordService,
    MailerService $mailerService
  ) {
    parent::__construct($logger, $utilisateurRepository);
    $this->passwordService = $passwordService;
    $this->mailerService = $mailerService;
  }

  protected function action(): Response
  {
    parent::action();
    $this->assertUtilisateurAuthEstAdmin();
    $body = $this->getFormData();

    if (!isset($body['identifiant'])) throw new HttpBadRequestException($this->request, "L'identifiant est obligatoire");
    if (!isset($body['email'])) throw new HttpBadRequestException($this->request, "L'email est obligatoire");
    $this->assertEstEmail($body['email']);
    if (!isset($body['nom'])) throw new HttpBadRequestException($this->request, "Le nom est obligatoire");
    if (!isset($body['genre'])) throw new HttpBadRequestException($this->request, "Le genre est obligatoire");
    if (!isset($body['estAdmin'])) $body['estAdmin'] = false;
    if (!isset($body['prefNotifIdees'])) $body['prefNotifIdees'] = PrefNotifIdees::Aucune;

    $mdp = $this->passwordService->randomPassword();
    $utilisateur = $this->utilisateurRepository->create(
      $body['identifiant'],
      $body['email'],
      password_hash($mdp, PASSWORD_DEFAULT),
      $body['nom'],
      $body['genre'],
      boolval($body['estAdmin']),
      $body['prefNotifIdees']
    );
  
    $this->mailerService->envoieMailCreationUtilisateur($this->request, $utilisateur, $mdp);

    return $this->respondWithData(new SerializableUtilisateur($utilisateur, true));
  }
}
