<?php

// src/Processor/IdeeSuppressionProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Idee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class IdeeSuppressionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Idee
    {
        $idee = $this->entityManager->getRepository(Idee::class)->find($uriVariables['id']);
        
        if (!$idee) {
            throw new BadRequestException('Idée introuvable');
        }

        $currentUser = $this->security->getUser();
        
        // Check if user is author or admin
        if ($idee->getAuteur() !== $currentUser && !$currentUser->isAdmin()) {
            throw new AccessDeniedHttpException("L'utilisateur authentifié n'est ni l'auteur de l'idée, ni un administrateur");
        }

        // Check if already deleted
        if ($idee->getDateSuppression()) {
            throw new BadRequestException('Idée déjà supprimée');
        }

        // Soft delete - set suppression date
        $idee->setDateSuppression(new \DateTime());
        
        $this->entityManager->flush();

        return $idee;
    }
}
