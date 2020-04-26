<?php

declare(strict_types=1);

namespace App\Application\Actions\ListeIdees;

use App\Application\Actions\Action;
use App\Application\Mock\MockData;
use Psr\Http\Message\ResponseInterface as Response;

class GetListeIdeesAction extends Action
{
  protected function action(): Response
  {
    $idUtilisateur = (int) $this->resolveArg('idUtilisateur');
    return $this->respondWithData(MockData::listesIdees[$idUtilisateur]);
  }
}
