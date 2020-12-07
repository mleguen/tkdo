<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Occasion;

use App\Domain\Occasion\Occasion;
use App\Domain\Utilisateur\Utilisateur;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="tkdo_occasion")
 */
class DoctrineOccasion implements Occasion
{
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var DateTime
     * @Column(type="datetime")
     */
    private $date;

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

    public function __construct(?int $id = NULL)
    {
        if (isset($id)) $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function addParticipant(Utilisateur $participant): Occasion
    {
        $this->participants->add($participant);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
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
        return $this->participants->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function setDate(DateTime $date): Occasion
    {
        $this->date = $date;
        return $this;
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
