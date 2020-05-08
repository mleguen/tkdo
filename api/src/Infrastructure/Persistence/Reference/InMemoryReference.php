<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Reference;

use App\Domain\Reference\Reference;

class InMemoryReference implements Reference
{
    /**
     * @var int
     */
    protected $id;

    public function __construct(
        int $id
    ) {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }
}
