<?php

declare(strict_types=1);

namespace App\Application\Actions\Connexion;

use App\Application\Actions\Action;
use App\Application\Mock\MockData;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class PostConnexionAction extends Action
{
  protected function action(): Response
  {
    $data = $this->getFormData();

    if (($data->identifiant !== MockData::alice["identifiant"]) || ($data->mdp !== MockData::alice["mdp"])) {
      $this->logger->warning("Erreur de connexion de $data->identifiant");
      throw new HttpBadRequestException($this->request, "identifiants invalides");
    }

    $this->logger->info("Utilisateur $data->identifiant connecté");
    return $this->respondWithData(["token" => MockData::token]);
  }
}
