<?php
declare(strict_types=1);

namespace App\Dom\Repository;

use App\Dom\Exception\IdeeInconnueException;
use App\Dom\Model\Idee;
use App\Dom\Model\Utilisateur;
use DateTime;

interface IdeeRepository
{
    public function create(
        Utilisateur $utilisateur,
        string $description,
        Utilisateur $auteur,
        DateTime $dateProposition
    ): Idee;

    /**
     * @throws IdeeInconnueException
     */
    public function read(int $id, bool $reference = false): Idee;

    /**
     * @return Idee[]
     */
    public function readAllByNotifPeriodique(Utilisateur $utilisateur, DateTime $dateNotif): array;

    /**
     * @return Idee[]
     */
    public function readAllByUtilisateur(Utilisateur $utilisateur, ?bool $supprimees = null): array;
    
    public function update(Idee $idee): Idee;
}
