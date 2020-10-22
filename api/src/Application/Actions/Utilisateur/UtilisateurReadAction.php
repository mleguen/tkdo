<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use App\Application\Serializable\Utilisateur\SerializableUtilisateur;
use Psr\Http\Message\ResponseInterface as Response;

class UtilisateurReadAction extends UtilisateurAction
{
  protected function action(): Response
  {
    parent::action();
    $utilisateur = $this->utilisateurRepository->read($this->idUtilisateur);
    return $this->respondWithData(new SerializableUtilisateur($utilisateur));
  }
}
