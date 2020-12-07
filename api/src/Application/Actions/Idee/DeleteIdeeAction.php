<?php

declare(strict_types=1);

namespace App\Application\Actions\Idee;

use Psr\Http\Message\ResponseInterface as Response;

class DeleteIdeeAction extends IdeeActionANotifier
{
  protected function action(): Response
  {
    $this->assertAuth();
    $idee = $this->ideeRepository->read((int) $this->resolveArg('idIdee'));
    $this->assertUtilisateurAuthEstAuteur($idee->getAuteur()->getId());

    $this->ideeRepository->delete($idee);

    $utilisateursANotifier = $this->utilisateurRepository->readAllByNotifInstantaneePourIdees(
      $idee->getUtilisateur()->getId(),
      $this->request->getAttribute('idUtilisateurAuth')
    );
    foreach ($utilisateursANotifier as $utilisateurANotifier) {
      $this->mailerService->envoieMailSuppressionIdee($this->request, $utilisateurANotifier, $idee->getUtilisateur(), $idee);
    }

    return $this->respondWithData();
  }
}
