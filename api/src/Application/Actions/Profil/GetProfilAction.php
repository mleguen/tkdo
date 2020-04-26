<?php

declare(strict_types=1);

namespace App\Application\Actions\Profil;

use App\Application\Actions\Action;
use App\Application\Mock\MockData;
use Psr\Http\Message\ResponseInterface as Response;

class GetProfilAction extends Action
{
  protected function action(): Response
  {
    $profil = MockData::alice;
    unset($profil["mdp"]);
    return $this->respondWithData($profil);
  }
}
