<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpForbiddenException;

class EditUtilisateurAction extends UtilisateurAction
{
  protected function action(): Response
  {
    parent::action();
    $body = $this->getFormData();
    $utilisateur = $this->utilisateurRepository->read($this->idUtilisateur)
      ->setIdentifiant($body->identifiant)
      ->setNom($body->nom);
    if (isset($body->genre)) $utilisateur->setGenre($body->genre);

    if (isset($body->mdp)) {
      if ($this->request->getAttribute('idUtilisateurAuth') !== $this->idUtilisateur) {
        $this->logger->warning("Seul l'utilisateur $this->idUtilisateur lui-même peut modifier son mot de passe");
        throw new HttpForbiddenException($this->request);
      }
      $utilisateur->setMdp($body->mdp);
    }
    
    if (isset($body->estAdmin) && ($body->estAdmin !== $utilisateur->getEstAdmin())) {
      if (!$this->request->getAttribute('estAdmin')) {
        $this->logger->warning("Seul un admin a le droit d'ajouter/enlever des droits admin");
        throw new HttpForbiddenException($this->request);
      }
      $utilisateur->setEstAdmin($body->estAdmin);
    }
    
    $this->utilisateurRepository->update($utilisateur);
    return $this->respondWithData();
  }
}
