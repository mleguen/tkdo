<?php

declare(strict_types=1);

namespace App\Application\Actions\Idee;

use App\Application\Actions\Idee\IdeeAction;
use App\Application\Serializable\Idee\SerializableIdee;
use App\Application\Serializable\Utilisateur\SerializableUtilisateur;
use App\Domain\Idee\Idee;
use Psr\Http\Message\ResponseInterface as Response;

class IdeeReadAllAction extends IdeeAction
{
  protected function action(): Response
  {
    parent::action();
    $utilisateur = $this->utilisateurRepository->read($this->idUtilisateur);
    return $this->respondWithData([
      "utilisateur" => new SerializableUtilisateur($utilisateur),
      "idees" => array_map(
        function (Idee $i) {
          return new SerializableIdee($i);
        },
        $this->ideeRepository->readByUtilisateur($utilisateur)
      ),
    ]);
  }
}
