<?php

declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\PrefNotifIdees;
use App\Dom\Model\Utilisateur;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="tkdo_utilisateur")
 */
class UtilisateurAdaptor implements Utilisateur
{
    /**
     * @var DateTime
     * @Column(type="datetime")
     */
    private $dateDerniereNotifPeriodique;

    /**
     * @var string
     * @Column()
     */
    private $email;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    private $admin = false;

    /**
     * @var string
     * @Column()
     */
    private $genre;

    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(unique=true)
     */
    private $identifiant;

    /**
     * @var string
     * @Column()
     */
    private $mdp;
    
    /**
     * @var string
     */
    private $mdpClair;

    /**
     * @var string
     * @Column()
     */
    private $nom;

    /**
     * @var ArrayCollection
     * @ManyToMany(targetEntity="App\Appli\ModelAdaptor\OccasionAdaptor", mappedBy="participants")
     */
    private $occasions;

    /**
     * @var string
     * @Column()
     */
    private $prefNotifIdees = PrefNotifIdees::Aucune;

    public function __construct(?int $id = NULL)
    {
        if (isset($id)) $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function estUtilisateur(Utilisateur $utilisateur): bool
    {
        return $utilisateur->getId() === $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateDerniereNotifPeriodique(): DateTime
    {
        return $this->dateDerniereNotifPeriodique;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdmin(): bool
    {
        return $this->admin;
    }

    /**
     * {@inheritdoc}
     */
    public function getGenre(): string
    {
        return $this->genre;
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
    public function getIdentifiant(): string
    {
        return $this->identifiant;
    }

    /**
     * {@inheritdoc}
     */
    public function getMdpClair(): string
    {
        return $this->mdpClair;
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
    public function getOccasions(): array
    {
        return $this->occasions->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefNotifIdees(): string
    {
        return $this->prefNotifIdees;
    }

    /**
     * {@inheritdoc}
     */
    public function setDateDerniereNotifPeriodique(DateTime $dateDerniereNotifPeriodique): Utilisateur
    {
        $this->dateDerniereNotifPeriodique = $dateDerniereNotifPeriodique;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail(string $email): Utilisateur
    {
        $this->email = $email;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAdmin(bool $admin): Utilisateur
    {
        $this->admin = $admin;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setGenre(string $genre): Utilisateur
    {
        $this->genre = $genre;
        return $this;
    }

    /**
     * Force l'id
     * 
     * Attention : ne pas tenter de persister l'entité par la suite !
     */
    public function setId(int $id): Utilisateur
    {
        $this->id = $id;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentifiant(string $identifiant): Utilisateur
    {
        $this->identifiant = $identifiant;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMdpClair(string $mdpClair): Utilisateur
    {
        $this->mdpClair = $mdpClair;
        $this->mdp = password_hash($mdpClair, PASSWORD_DEFAULT);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNom(string $nom): Utilisateur
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefNotifIdees(string $prefNotifIdees): Utilisateur
    {
        $this->prefNotifIdees = $prefNotifIdees;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function verifieMdp(string $mdpClair): bool
    {
        return password_verify($mdpClair, $this->mdp);
    }
}
