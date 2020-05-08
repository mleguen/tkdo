<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\ResultatTirage;

use App\Domain\Occasion\Occasion;
use App\Domain\ResultatTirage\ResultatTirage;
use App\Domain\Utilisateur\Utilisateur;
use App\Infrastructure\Persistence\Reference\InMemoryReference;

class InMemoryResultatTirage extends InMemoryReference implements ResultatTirage
{
    /**
     * @var Occasion
     */
    private $occasion;

    /**
     * @var Utilisateur
     */
    private $quiOffre;

    /**
     * @var Utilisateur
     */
    private $quiRecoit;

    public function __construct(
        Occasion $occasion,
        Utilisateur $quiOffre,
        Utilisateur $quiRecoit
    ) {
        $this->occasion = $occasion;
        $this->quiOffre = $quiOffre;
        $this->quiRecoit = $quiRecoit;
    }

    public function getOccasion(): Occasion
    {
        return $this->occasion;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuiOffre(): Utilisateur
    {
        return $this->quiOffre;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuiRecoit(): Utilisateur
    {
        return $this->quiRecoit;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->occasion = clone $this->occasion;
        $this->quiOffre = clone $this->quiOffre;
        $this->quiRecoit = clone $this->quiRecoit;
    }
}
