<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Idee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IdeeSuppressionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Idee
    {
        // The standard Idee provider provides the entity in $data
        if (!$data instanceof Idee) {
            throw new NotFoundHttpException('Idée non trouvée');
        }

        $idee = $data;

        // Soft delete
        $idee->setSupprimee(true);
        $idee->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $idee;
    }
}
