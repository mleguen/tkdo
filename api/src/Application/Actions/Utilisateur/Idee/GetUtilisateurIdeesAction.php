<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur\Idee;

use App\Application\Actions\Utilisateur\Idee\UtilisateurIdeeAction;
use Psr\Http\Message\ResponseInterface as Response;

class GetUtilisateurIdeesAction extends UtilisateurIdeeAction
{
  protected function action(): Response
  {
    parent::action();
    return $this->respondWithData([
      "utilisateur" => $this->utilisateur,
      "idees" => $this->ideeRepository->findAllByUtilisateur($this->utilisateur),
    ]);
  }
}
