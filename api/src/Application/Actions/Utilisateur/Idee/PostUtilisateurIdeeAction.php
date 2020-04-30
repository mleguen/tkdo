<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur\Idee;

use App\Application\Actions\Utilisateur\Idee\UtilisateurIdeeAction;
use App\Domain\Idee\Idee;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class PostUtilisateurIdeeAction extends UtilisateurIdeeAction
{
  protected function action(): Response
  {
    parent::action();
    $body = $this->getFormData();

    try {
      $auteur = $this->utilisateurRepository->find($body->idAuteur);
    }
    // Interception pour éviter une 404 (inconnu) alors qu'il s'agit d'une 400 (mauvaise requête)
    catch (UtilisateurInconnuException $err) {
      throw new HttpBadRequestException($this->request, "auteur inconnu");
    }

    $this->ideeRepository->persist(new Idee(
      null,
      $this->utilisateur,
      $body->description,
      $auteur
    ));

    return $this->respondWithData();
  }
}
