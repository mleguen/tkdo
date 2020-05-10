<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Reference;

use App\Domain\Reference\Reference;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;

/** @MappedSuperclass */
class DoctrineReference implements Reference
{
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    public function __construct(?int $id = NULL)
    {
        if (isset($id)) $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }
}
