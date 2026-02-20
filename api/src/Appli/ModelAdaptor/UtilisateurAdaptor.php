<?php

declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\Occasion;
use App\Dom\Model\PrefNotifIdees;
use App\Dom\Model\Utilisateur;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @Column(type="datetime")
     */
    private DateTime $dateDerniereNotifPeriodique;

    /**
     * @Column()
     */
    private string $email;

    /**
     * @Column(type="boolean")
     */
    private bool $admin = false;

    /**
     * @Column()
     */
    private string $genre;

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected int $id;

    /**
     * @Column(unique=true)
     */
    private string $identifiant;

    /**
     * @Column()
     */
    private string $mdp;

    private string $mdpClair;

    /**
     * @Column()
     */
    private string $nom;

    /**
     * @var Collection<int, OccasionAdaptor>
     * @ManyToMany(targetEntity="App\Appli\ModelAdaptor\OccasionAdaptor", mappedBy="participants")
     */
    private Collection $occasions;

    /**
     * @Column()
     */
    private string $prefNotifIdees = PrefNotifIdees::Aucune;

    /**
     * @Column(name="tentatives_echouees", type="integer")
     */
    private int $tentativesEchouees = 0;

    /**
     * @Column(name="verrouille_jusqua", type="datetime", nullable=true)
     * @phpstan-ignore property.unusedType (Doctrine-hydrated; lockout logic in Story 1.4)
     */
    private ?DateTime $verrouilleJusqua = null;

    public function __construct(?int $id = NULL)
    {
        if (isset($id)) $this->id = $id;
        $this->occasions = new ArrayCollection();
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
    #[\Override]
    public function getTentativesEchouees(): int
    {
        return $this->tentativesEchouees;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getVerrouilleJusqua(): ?DateTime
    {
        return $this->verrouilleJusqua;
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
    #[\Override]
    public function incrementeTentativesEchouees(): void
    {
        $this->tentativesEchouees++;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function reinitialiserTentativesEchouees(): void
    {
        $this->tentativesEchouees = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function verifieMdp(string $mdpClair): bool
    {
        return password_verify($mdpClair, $this->mdp);
    }
}
