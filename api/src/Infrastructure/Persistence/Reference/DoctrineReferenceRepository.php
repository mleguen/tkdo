<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Reference;

use App\Domain\Reference\Reference;
use App\Domain\Reference\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;

class DoctrineReferenceRepository implements ReferenceRepository
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    public function __construct(EntityManager $em, string $entityName)
    {
        $this->em = $em;
        $this->entityName = $entityName;
        $this->repository = $this->em->getRepository($entityName);
    }

    /**
     * {@inheritdoc}
     */
    public function getReference(int $id): Reference
    {
        return $this->em->getReference($this->entityName, $id);
    }
}
