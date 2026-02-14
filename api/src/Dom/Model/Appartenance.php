<?php
declare(strict_types=1);

namespace App\Dom\Model;

use DateTime;

interface Appartenance
{
    public function getGroupe(): Groupe;
    public function getUtilisateur(): Utilisateur;
    public function getEstAdmin(): bool;
    public function getDateAjout(): DateTime;

    public function setGroupe(Groupe $groupe): Appartenance;
    public function setUtilisateur(Utilisateur $utilisateur): Appartenance;
    public function setEstAdmin(bool $estAdmin): Appartenance;
    public function setDateAjout(DateTime $dateAjout): Appartenance;
}
