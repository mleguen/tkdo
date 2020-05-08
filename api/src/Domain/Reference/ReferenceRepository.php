<?php
declare(strict_types=1);

namespace App\Domain\Reference;

use App\Domain\Reference\Reference;

interface ReferenceRepository
{
    public function getReference(int $id): Reference;
}
