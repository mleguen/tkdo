<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use Psr\Http\Message\ResponseInterface as Response;

class EditUtilisateurAction extends UtilisateurAction
{
  protected function action(): Response
  {
    parent::action();
    $body = $this->getFormData();
    $utilisateur = $this->utilisateurRepository->read($this->idUtilisateur)
      ->setIdentifiant($body->identifiant)
      ->setNom($body->nom);
    if (isset($body->genre)) $utilisateur->setGenre($body->genre);
    if (isset($body->mdp)) $utilisateur->setMdp($body->mdp);
    $this->utilisateurRepository->update($utilisateur);
    return $this->respondWithData();
  }
}
