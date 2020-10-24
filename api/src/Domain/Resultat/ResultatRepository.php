<?php
declare(strict_types=1);

namespace App\Domain\Resultat;

interface ResultatRepository
{
    /**
     * @return Resultat[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     * @return Resultat
     * @throws ResultatNotFoundException
     */
    public function findResultatOfId(int $id): Resultat;
}
