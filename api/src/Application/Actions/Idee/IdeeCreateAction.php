<?php

declare(strict_types=1);

namespace App\Application\Actions\Idee;

use App\Application\Actions\Idee\IdeeAction;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class IdeeCreateAction extends IdeeAction
{
  protected function action(): Response
  {
    parent::action();
    $body = $this->getFormData();

    try {
      $auteur = $this->utilisateurRepository->read($body->idAuteur);
    }
    // Interception pour éviter une 404 (inconnu) alors qu'il s'agit d'une 400 (mauvaise requête)
    catch (UtilisateurInconnuException $err) {
      throw new HttpBadRequestException($this->request, "auteur inconnu");
    }

    $this->ideeRepository->create(
      $this->utilisateur,
      $body->description,
      $auteur,
      new \DateTime()
    );

    return $this->respondWithData();
  }
}
