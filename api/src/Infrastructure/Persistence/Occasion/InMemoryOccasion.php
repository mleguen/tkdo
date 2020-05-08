<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Occasion;

use App\Domain\Occasion\Occasion;
use App\Domain\Utilisateur\Utilisateur;
use App\Infrastructure\Persistence\Reference\InMemoryReference;

class InMemoryOccasion extends InMemoryReference implements Occasion
{
    /**
     * @var string
     */
    private $titre;

    /**
     * @var Utilisateur[]
     */
    private $participants;

    /**
     * @param Utilisateur[] $participants
     */
    public function __construct(
        int $id,
        string $titre,
        array $participants
    ) {
        parent::__construct($id);
        $this->titre = $titre;
        $this->participants = $participants;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitre(): string
    {
        return $this->titre;
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipants(): array
    {
        return $this->participants;   
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->participants = array_map(function ($o) { return clone $o; }, $this->participants);
    }
}
