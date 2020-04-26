<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use Psr\Http\Message\ResponseInterface as Response;

class PutUtilisateurAction extends UtilisateurAction
{
  protected function action(): Response
  {
    parent::action();
    $utilisateurModifie = $this->getFormData();
    $utilisateur = $this->utilisateurRepository->find($this->idUtilisateur);
    $utilisateur
      ->setIdentifiant($utilisateurModifie->identifiant)
      ->setNom($utilisateurModifie->nom);
    if (isset($utilisateurModifie->mdp)) $utilisateur->setMdp($utilisateurModifie->mdp);
    // TODO: persist()
    return $this->respondWithData();
  }
}
