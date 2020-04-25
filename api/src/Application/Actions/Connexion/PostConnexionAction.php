<?php

declare(strict_types=1);

namespace App\Application\Actions\Connexion;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;

class mock
{
  const alice = [
    "identifiant" => "alice@tkdo.org",
    "nom" => "Alice",
    "mdp" => "Alice",
  ];

  const token = "fake-jwt-token";
}

class PostConnexionAction extends Action
{
  protected function action(): Response
  {
    $data = $this->getFormData();

    if (($data->identifiant !== mock::alice["identifiant"]) || ($data->mdp !== mock::alice["mdp"])) {
      $this->logger->warning("Erreur de connexion de $data->identifiant");
      throw new HttpBadRequestException($this->request, "identifiants invalides");
    }

    $this->logger->info("Utilisateur $data->identifiant connecté");
    return $this->respondWithData(["token" => mock::token]);
  }
}
