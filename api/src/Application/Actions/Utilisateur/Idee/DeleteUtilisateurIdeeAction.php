<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur\Idee;

use App\Application\Actions\Utilisateur\Idee\UtilisateurIdeeAction;
use Psr\Http\Message\ResponseInterface as Response;

class DeleteUtilisateurIdeeAction extends UtilisateurIdeeAction
{
  protected function action(): Response
  {
    parent::action();
    // TODO: obtenir une référence à la place pour éviter une lecture en base
    $idee = $this->ideeRepository->find((int) $this->resolveArg('idIdee'));
    $this->ideeRepository->remove($idee);
    return $this->respondWithData();
  }
}
