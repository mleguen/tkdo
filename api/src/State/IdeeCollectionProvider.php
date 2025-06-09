<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\IdeeRepository;

class IdeeCollectionProvider implements ProviderInterface
{
    public function __construct(
        private IdeeRepository $ideeRepository
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Only return non-deleted ideas
        return $this->ideeRepository->findNonDeleted();
    }
}
