<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use App\Application\Serializable\Utilisateur\SerializableUtilisateur;
use App\Domain\Utilisateur\PrefNotifIdees;
use DateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

class EditUtilisateurAction extends OneUtilisateurAction
{
  protected function action(): Response
  {
    parent::action();
    $body = $this->getFormData();
    $utilisateur = $this->utilisateurRepository->read($this->idUtilisateur);
    if (isset($body['identifiant'])) $utilisateur->setIdentifiant($body['identifiant']);
    if (isset($body['email'])) {
      $this->assertEstEmail($body['email']);
      $utilisateur->setEmail($body['email']);
    }
    if (isset($body['nom'])) $utilisateur->setNom($body['nom']);
    if (isset($body['genre'])) $utilisateur->setGenre($body['genre']);

    if (isset($body['mdp'])) {
      if ($this->request->getAttribute('idUtilisateurAuth') !== $this->idUtilisateur) {
        $this->logger->warning("Seul l'utilisateur $this->idUtilisateur lui-même peut modifier son mot de passe");
        throw new HttpForbiddenException($this->request);
      }
      $utilisateur->setMdp(password_hash($body['mdp'], PASSWORD_DEFAULT));
    }
    
    if (isset($body['estAdmin']) && (boolval($body['estAdmin']) !== $utilisateur->getEstAdmin())) {
      if (!$this->request->getAttribute('estAdmin')) {
        $this->logger->warning("Seul un admin a le droit d'ajouter/enlever des droits admin");
        throw new HttpForbiddenException($this->request);
      }
      $utilisateur->setEstAdmin(boolval($body['estAdmin']));
    }

    if (isset($body['prefNotifIdees'])) {
      if (!in_array($body['prefNotifIdees'], PrefNotifIdees::Toutes)) {
        throw new HttpBadRequestException($this->request, "Format de préférence de notification incorrect : {$body['prefNotifIdees']}");
      }
      // Si on passe à une notification périodique, on réinitialise la date de dernière notification
      if (
        !in_array($utilisateur->getPrefNotifIdees(), PrefNotifIdees::Periodiques) &&
        in_array($body['prefNotifIdees'], PrefNotifIdees::Periodiques)
      ) {
        $utilisateur->setDateDerniereNotifPeriodique(new DateTime());
      }
      $utilisateur->setPrefNotifIdees($body['prefNotifIdees']);
    }
    
    $utilisateur = $this->utilisateurRepository->update($utilisateur);
    return $this->respondWithData(new SerializableUtilisateur($utilisateur, true));
  }
}
