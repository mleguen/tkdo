<?php
declare(strict_types=1);

namespace App\Dom\Repository;

use App\Dom\Exception\GroupeInconnuException;
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

    public function update(Groupe $groupe): Groupe;
}
