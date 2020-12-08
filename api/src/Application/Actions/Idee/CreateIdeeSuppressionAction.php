<?php

declare(strict_types=1);

namespace App\Application\Actions\Idee;

use App\Application\Serializable\Idee\SerializableIdee;
use DateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class CreateIdeeSuppressionAction extends IdeeActionANotifier
{
  protected function action(): Response
  {
    $this->assertAuth();
    $idee = $this->ideeRepository->read((int) $this->resolveArg('idIdee'));
    $this->assertUtilisateurAuthEstAuteur($idee->getAuteur()->getId());
    
    if ($idee->hasDateSuppression()) throw new HttpBadRequestException($this->request, "L'idée {$idee->getId()} a déjà été supprimée");

    $idee->setDateSuppression(new DateTime());
    $this->ideeRepository->update($idee);

    $utilisateursANotifier = $this->utilisateurRepository->readAllByNotifInstantaneePourIdees(
      $idee->getUtilisateur()->getId(),
      $this->request->getAttribute('idUtilisateurAuth')
    );
    foreach ($utilisateursANotifier as $utilisateurANotifier) {
      $this->mailerService->envoieMailIdeeSuppression($utilisateurANotifier, $idee->getUtilisateur(), $idee);
    }

    return $this->respondWithData(new SerializableIdee($idee));
  }
}
