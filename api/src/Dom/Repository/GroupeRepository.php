<?php
declare(strict_types=1);

namespace App\Dom\Repository;

use App\Dom\Exception\GroupeInconnuException;
use App\Dom\Model\Appartenance;
use App\Dom\Model\Groupe;

interface GroupeRepository
{
    public function create(string $nom): Groupe;

    /**
     * @throws GroupeInconnuException
     */
    public function read(int $id): Groupe;

    /**
     * @return Groupe[]
     */
    public function readAll(): array;

    /**
     * @return Appartenance[]
     */
    public function readAppartenancesForUtilisateur(int $utilisateurId): array;

    /**
     * Returns ALL group memberships for a user, including archived groups.
     * Unlike readAppartenancesForUtilisateur(), does NOT filter by archive status.
     * Results are sorted alphabetically by group name (ASC).
     *
     * @return Appartenance[]
     */
    public function readToutesAppartenancesForUtilisateur(int $utilisateurId): array;

    public function update(Groupe $groupe): Groupe;
}
