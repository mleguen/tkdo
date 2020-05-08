<?php

declare(strict_types=1);

namespace App\Application\Actions\Idee;

use App\Application\Actions\Idee\IdeeAction;
use Psr\Http\Message\ResponseInterface as Response;

class IdeeDeleteAction extends IdeeAction
{
  protected function action(): Response
  {
    parent::action();
    $idee = $this->ideeRepository->getReference((int) $this->resolveArg('idIdee'));
    $this->ideeRepository->delete($idee);
    return $this->respondWithData();
  }
}
