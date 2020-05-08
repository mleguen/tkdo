<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Reference;

use App\Domain\Reference\Reference;
use App\Infrastructure\Persistence\Reference\InMemoryReference;

class InMemoryReferenceRepository
{
    public function getReference(int $id): Reference
    {
        return new InMemoryReference($id);
    }
}
