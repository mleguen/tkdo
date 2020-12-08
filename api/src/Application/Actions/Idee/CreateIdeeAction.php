<?php

declare(strict_types=1);

namespace App\Application\Actions\Idee;

use App\Application\Serializable\Idee\SerializableIdee;
use DateTime;
use Psr\Http\Message\ResponseInterface as Response;

class CreateIdeeAction extends IdeeActionANotifier
{
  protected function action(): Response
  {
    $this->assertAuth();
    $body = $this->getFormData();
    $this->assertUtilisateurAuthEstAuteur($body['idAuteur']);

    $utilisateur = $this->utilisateurRepository->read($body['idUtilisateur']);

    $idee = $this->ideeRepository->create(
      $utilisateur,
      $body['description'],
      $this->utilisateurRepository->read($body['idAuteur'], true),
      new DateTime()
    );

    $utilisateursANotifier = $this->utilisateurRepository->readAllByNotifInstantaneePourIdees(
      $body['idUtilisateur'],
      $this->request->getAttribute('idUtilisateurAuth')
    );
    foreach ($utilisateursANotifier as $utilisateurANotifier) {
      $this->mailerService->envoieMailIdeeCreation($utilisateurANotifier, $utilisateur, $idee);
    }

    return $this->respondWithData(new SerializableIdee($idee));
  }
}
