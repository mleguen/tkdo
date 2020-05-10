<?php
declare(strict_types=1);

namespace App\Domain\Idee;

use App\Domain\Reference\Reference;
use App\Domain\Reference\ReferenceRepository;
use App\Domain\Utilisateur\Utilisateur;

interface IdeeRepository extends ReferenceRepository
{
    public function create(
        Utilisateur $utilisateur,
        string $description,
        Utilisateur $auteur,
        \DateTime $dateProposition
    ): Idee;

    /**
     * @throws IdeeInconnueException
     */
    public function read(int $id): Idee;

    /**
     * @return Idee[]
     */
    public function readByUtilisateur(Utilisateur $utilisateur): array;

    /**
     * @throws IdeeInconnueException
     */
    public function delete(Reference $idee);
}
