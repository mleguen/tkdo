<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use Psr\Http\Message\ResponseInterface as Response;

class GetUtilisateurAction extends UtilisateurAction
{
  protected function action(): Response
  {
    parent::action();
    // TODO: vérifier que l'id correspond à celui du JWT
    return $this->respondWithData($this->utilisateur);
  }
}
