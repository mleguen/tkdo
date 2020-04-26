<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use Psr\Http\Message\ResponseInterface as Response;

class GetUtilisateurAction extends UtilisateurAction
{
  protected function action(): Response
  {
    parent::action();
    $utilisateur = $this->utilisateurRepository->find($this->idUtilisateur);
    return $this->respondWithData($utilisateur->getUtilisateurSansMdp());
  }
}
