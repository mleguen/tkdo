<?php

declare(strict_types=1);

namespace Test\Builder;

use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use App\Dom\Model\Genre;
use App\Dom\Model\PrefNotifIdees;
use DateTime;
use Doctrine\ORM\EntityManager;

/**
 * Fluent builder for creating test Utilisateur entities
 *
 * Note: The static counter is not thread-safe. PHPUnit runs tests sequentially
 * by default, so this is not an issue for current test execution.
 *
 * Usage:
 *   $user = UtilisateurBuilder::aUser()
 *       ->withIdentifiant('alice')
 *       ->withAdmin(true)
 *       ->build();
 *
 *   $user = UtilisateurBuilder::aUser()
 *       ->withEmail('bob@example.com')
 *       ->persist($em);
 */
class UtilisateurBuilder
{
    private static int $counter = 0;

    private string $identifiant;
    private string $email;
    private bool $admin = false;
    private string $genre = Genre::Masculin;
    private string $nom;
    private DateTime $dateDerniereNotifPeriodique;
    private string $prefNotifIdees = PrefNotifIdees::Aucune;
    private ?string $mdpClair = null;

    private function __construct()
    {
        self::$counter++;
        $this->identifiant = 'user' . self::$counter;
        $this->email = $this->identifiant . '@localhost';
        $this->nom = ucfirst($this->identifiant);
        $this->dateDerniereNotifPeriodique = new DateTime();
        $this->mdpClair = 'mdp' . $this->identifiant;
    }

    /**
     * Create a new UtilisateurBuilder with default values
     */
    public static function aUser(): self
    {
        return new self();
    }

    /**
     * Set the identifiant (username)
     */
    public function withIdentifiant(string $identifiant): self
    {
        $oldIdentifiant = $this->identifiant;
        $this->identifiant = $identifiant;
        // Update dependent defaults
        if ($this->email === $oldIdentifiant . '@localhost') {
            $this->email = $identifiant . '@localhost';
        }
        if ($this->nom === ucfirst($oldIdentifiant)) {
            $this->nom = ucfirst($identifiant);
        }
        if ($this->mdpClair === 'mdp' . $oldIdentifiant) {
            $this->mdpClair = 'mdp' . $identifiant;
        }
        return $this;
    }

    /**
     * Set the email address
     */
    public function withEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Set the admin status
     */
    public function withAdmin(bool $admin = true): self
    {
        $this->admin = $admin;
        return $this;
    }

    /**
     * Set the genre (gender)
     */
    public function withGenre(string $genre): self
    {
        $this->genre = $genre;
        return $this;
    }

    /**
     * Set the nom (display name)
     */
    public function withNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * Set the date of last periodic notification
     */
    public function withDateDerniereNotifPeriodique(DateTime $date): self
    {
        $this->dateDerniereNotifPeriodique = $date;
        return $this;
    }

    /**
     * Set the notification preference for ideas
     */
    public function withPrefNotifIdees(string $prefNotifIdees): self
    {
        $this->prefNotifIdees = $prefNotifIdees;
        return $this;
    }

    /**
     * Set the clear text password
     */
    public function withMdpClair(string $mdpClair): self
    {
        $this->mdpClair = $mdpClair;
        return $this;
    }

    /**
     * Build the Utilisateur entity in memory (not persisted)
     */
    public function build(): UtilisateurAdaptor
    {
        $utilisateur = new UtilisateurAdaptor();
        $utilisateur
            ->setIdentifiant($this->identifiant)
            ->setEmail($this->email)
            ->setAdmin($this->admin)
            ->setGenre($this->genre)
            ->setNom($this->nom)
            ->setDateDerniereNotifPeriodique($this->dateDerniereNotifPeriodique)
            ->setPrefNotifIdees($this->prefNotifIdees);

        if ($this->mdpClair !== null) {
            $utilisateur->setMdpClair($this->mdpClair);
        }

        return $utilisateur;
    }

    /**
     * Build and persist the Utilisateur entity to the database
     */
    public function persist(EntityManager $em): UtilisateurAdaptor
    {
        $utilisateur = $this->build();
        $em->persist($utilisateur);
        $em->flush();
        return $utilisateur;
    }

    /**
     * Reset the counter (useful for test isolation)
     */
    public static function resetCounter(): void
    {
        self::$counter = 0;
    }
}
