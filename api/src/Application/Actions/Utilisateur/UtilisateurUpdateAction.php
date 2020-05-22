<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use Psr\Http\Message\ResponseInterface as Response;

class UtilisateurUpdateAction extends UtilisateurAction
{
  protected function action(): Response
  {
    parent::action();
    // TODO: vérifier que l'id correspond à celui du JWT
    $body = $this->getFormData();
    $utilisateur = $this->utilisateurRepository->read($this->idUtilisateur)
      ->setIdentifiant($body->identifiant)
      ->setNom($body->nom);
    if (isset($body->mdp)) $utilisateur->setMdp($body->mdp);
    $this->utilisateurRepository->update($utilisateur);
    return $this->respondWithData();
  }
}
