<?php

declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\Occasion;
use App\Dom\Model\Utilisateur;
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
class OccasionAdaptor implements Occasion
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
     * @ManyToMany(targetEntity="App\Appli\ModelAdaptor\UtilisateurAdaptor", inversedBy="occasions")
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
     * {@inheritdoc}
     */
    public function setParticipants (array $participants): Occasion
    {
        $this->participants = new ArrayCollection($participants);
        return $this;
    }
}
