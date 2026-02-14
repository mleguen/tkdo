<?php

declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\Appartenance;
use App\Dom\Model\Groupe;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="tkdo_groupe")
 */
class GroupeAdaptor implements Groupe
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected int $id;

    /**
     * @Column()
     */
    private string $nom;

    /**
     * @Column(type="boolean")
     */
    private bool $archive = false;

    /**
     * @Column(type="datetime", name="date_creation")
     */
    private DateTime $dateCreation;

    /**
     * @var Collection<int, AppartenanceAdaptor>
     * @OneToMany(targetEntity="App\Appli\ModelAdaptor\AppartenanceAdaptor", mappedBy="groupe")
     */
    private Collection $appartenances;

    public function __construct(?int $id = NULL)
    {
        if (isset($id)) $this->id = $id;
        $this->appartenances = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function addAppartenance(Appartenance $appartenance): Groupe
    {
        assert($appartenance instanceof AppartenanceAdaptor);
        $this->appartenances->add($appartenance);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchive(): bool
    {
        return $this->archive;
    }

    /**
     * {@inheritdoc}
     */
    public function getAppartenances(): array
    {
        return $this->appartenances->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getDateCreation(): DateTime
    {
        return $this->dateCreation;
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
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * {@inheritdoc}
     */
    public function setArchive(bool $archive): Groupe
    {
        $this->archive = $archive;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDateCreation(DateTime $dateCreation): Groupe
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    /**
     * Force l'id
     *
     * Attention : ne pas tenter de persister l'entité par la suite !
     */
    public function setId(int $id): Groupe
    {
        $this->id = $id;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNom(string $nom): Groupe
    {
        $this->nom = $nom;
        return $this;
    }
}
