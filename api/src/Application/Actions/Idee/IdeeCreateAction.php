<?php

declare(strict_types=1);

namespace App\Application\Actions\Idee;

use App\Application\Actions\Idee\IdeeAction;
use Psr\Http\Message\ResponseInterface as Response;

class IdeeCreateAction extends IdeeAction
{
  protected function action(): Response
  {
    parent::action();
    $body = $this->getFormData();

    $this->ideeRepository->create(
      $this->utilisateurRepository->read($this->idUtilisateur, true),
      $body->description,
      $this->utilisateurRepository->read($body->idAuteur, true),
      new \DateTime()
    );

    return $this->respondWithData();
  }
}
