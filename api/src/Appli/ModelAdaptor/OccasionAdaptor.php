<?php

declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\Occasion;
use App\Dom\Model\Utilisateur;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
class OccasionAdaptor implements Occasion
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected int $id;

    /**
     * @Column(type="datetime")
     */
    private DateTime $date;

    /**
     * @Column()
     */
    private string $titre;

    /**
     * @var Collection<int, UtilisateurAdaptor>
     * @ManyToMany(targetEntity="App\Appli\ModelAdaptor\UtilisateurAdaptor", inversedBy="occasions")
     * @JoinTable(name="tkdo_participation")
     */
    private Collection $participants;

    public function __construct(?int $id = NULL)
    {
        if (isset($id)) $this->id = $id;
        $this->participants = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function addParticipant(Utilisateur $participant): Occasion
    {
        assert($participant instanceof UtilisateurAdaptor);
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
     * Force l'id
     * 
     * Attention : ne pas tenter de persister l'entité par la suite !
     */
    public function setId(int $id): Occasion
    {
        $this->id = $id;
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
     * @param Utilisateur[] $participants
     */
    public function setParticipants (array $participants): Occasion
    {
        assert(count($participants) === 0 || $participants[array_key_first($participants)] instanceof UtilisateurAdaptor);
        /** @var UtilisateurAdaptor[] $participants */
        $this->participants = new ArrayCollection(array_values($participants));
        return $this;
    }
}
