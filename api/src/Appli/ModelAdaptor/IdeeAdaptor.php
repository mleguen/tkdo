<?php

declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\Idee;
use App\Dom\Model\Utilisateur;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="tkdo_idee")
 */
class IdeeAdaptor implements Idee
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected int $id;

    /**
     * @ManyToOne(targetEntity="App\Appli\ModelAdaptor\UtilisateurAdaptor")
     * @JoinColumn(nullable=false)
     */
    private Utilisateur $utilisateur;

    /**
     * @Column()
     */
    private string $description;

    /**
     * @ManyToOne(targetEntity="App\Appli\ModelAdaptor\UtilisateurAdaptor")
     * @JoinColumn(nullable=false)
     */
    private Utilisateur $auteur;

    /**
     * @Column(type="datetime")
     */
    private DateTime $dateProposition;

    /**
     * @Column(type="datetime", nullable=true)
     */
    private ?DateTime $dateSuppression = null;

    public function __construct(?int $id = NULL)
    {
        if (isset($id)) $this->id = $id;
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
    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuteur(): Utilisateur
    {
        return $this->auteur;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateProposition(): DateTime
    {
        return $this->dateProposition;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateSuppression(): ?DateTime
    {
        return $this->dateSuppression;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDateSuppression(): bool
    {
        return isset($this->dateSuppression);
    }

    /**
     * Force l'id
     * 
     * Attention : ne pas tenter de persister l'entité par la suite !
     */
    public function setId(int $id): Idee
    {
        $this->id = $id;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUtilisateur(Utilisateur $utilisateur): Idee
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription(string $description): Idee
    {
        $this->description = $description;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setAuteur(Utilisateur $auteur): Idee
    {
        $this->auteur = $auteur;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setDateProposition(DateTime $dateProposition): Idee
    {
        $this->dateProposition = $dateProposition;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setDateSuppression(?DateTime $dateSuppression): Idee
    {
        $this->dateSuppression = $dateSuppression;
        return $this;
    }
}
