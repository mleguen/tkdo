<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Occasion;

use App\Domain\Occasion\Occasion;
use App\Infrastructure\Persistence\Reference\DoctrineReference;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="tkdo_occasion")
 */
class DoctrineOccasion extends DoctrineReference implements Occasion
{
    /**
     * @var string
     * @Column()
     */
    private $titre;

    /**
     * @var ArrayCollection
     * @ManyToMany(targetEntity="App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur")
     * @JoinTable(name="tkdo_participation")
     */
    private $participants;

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
        return $this->participants->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function setTitre (string $titre): Occasion
    {
        $this->titre = $titre;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParticipants (array $participants): Occasion
    {
        $this->participants = new ArrayCollection($participants);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        if (isset($this->participants)) $this->participants = new ArrayCollection(array_map(
            function ($o) { return clone $o; },
            $this->participants->toArray()
        ));
    }
}
