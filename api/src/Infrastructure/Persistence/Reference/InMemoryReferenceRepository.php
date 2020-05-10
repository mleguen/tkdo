<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Reference;

use App\Domain\Reference\Reference;
use App\Domain\Reference\ReferenceRepository;
use App\Infrastructure\Persistence\Reference\DoctrineReference;

class InMemoryReferenceRepository implements ReferenceRepository
{
    /**
     * {@inheritdoc}
     */
    public function getReference(int $id): Reference
    {
        return new DoctrineReference($id);
    }
}
