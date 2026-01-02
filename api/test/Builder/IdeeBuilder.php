<?php

declare(strict_types=1);

namespace Test\Builder;

use App\Appli\ModelAdaptor\IdeeAdaptor;
use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use DateTime;
use Doctrine\ORM\EntityManager;

/**
 * Fluent builder for creating test Idee entities
 *
 * Usage:
 *   $idee = IdeeBuilder::anIdee()
 *       ->forUtilisateur($recipient)
 *       ->byAuteur($author)
 *       ->withDescription('Un livre')
 *       ->build();
 *
 *   $idee = IdeeBuilder::anIdee()
 *       ->forUtilisateur($user1)
 *       ->byAuteur($user2)
 *       ->deleted()
 *       ->persist($em);
 */
class IdeeBuilder
{
    private static int $counter = 0;

    private ?UtilisateurAdaptor $utilisateur = null;
    private string $description;
    private ?UtilisateurAdaptor $auteur = null;
    private DateTime $dateProposition;
    private ?DateTime $dateSuppression = null;

    private function __construct()
    {
        self::$counter++;
        $this->description = 'Idée ' . self::$counter;
        $this->dateProposition = new DateTime();
    }

    /**
     * Create a new IdeeBuilder with default values
     */
    public static function anIdee(): self
    {
        return new self();
    }

    /**
     * Set the utilisateur (recipient of the gift idea)
     */
    public function forUtilisateur(UtilisateurAdaptor $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    /**
     * Set the description
     */
    public function withDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the auteur (author of the idea)
     */
    public function byAuteur(UtilisateurAdaptor $auteur): self
    {
        $this->auteur = $auteur;
        return $this;
    }

    /**
     * Set the date of proposition
     */
    public function withDateProposition(DateTime $dateProposition): self
    {
        $this->dateProposition = $dateProposition;
        return $this;
    }

    /**
     * Set the date of suppression (soft delete)
     */
    public function withDateSuppression(?DateTime $dateSuppression): self
    {
        $this->dateSuppression = $dateSuppression;
        return $this;
    }

    /**
     * Mark the idea as deleted (soft delete with current date)
     */
    public function deleted(): self
    {
        $this->dateSuppression = new DateTime();
        return $this;
    }

    /**
     * Build the Idee entity in memory (not persisted)
     *
     * Note: If utilisateur or auteur are not set, default users will be created.
     * For integration tests, consider setting these explicitly or persisting them first.
     */
    public function build(): IdeeAdaptor
    {
        // Ensure required fields are set with defaults if not provided
        $utilisateur = $this->utilisateur ?? UtilisateurBuilder::aUser()->build();
        $auteur = $this->auteur ?? UtilisateurBuilder::aUser()->build();

        $idee = new IdeeAdaptor();
        $idee
            ->setUtilisateur($utilisateur)
            ->setDescription($this->description)
            ->setAuteur($auteur)
            ->setDateProposition($this->dateProposition)
            ->setDateSuppression($this->dateSuppression);

        return $idee;
    }

    /**
     * Build and persist the Idee entity to the database
     *
     * Note: This will also persist utilisateur and auteur if they are not yet persisted.
     */
    public function persist(EntityManager $em): IdeeAdaptor
    {
        // Ensure utilisateur and auteur exist in database
        if ($this->utilisateur !== null && !$em->contains($this->utilisateur)) {
            $em->persist($this->utilisateur);
        }
        if ($this->auteur !== null && !$em->contains($this->auteur)) {
            $em->persist($this->auteur);
        }

        $idee = $this->build();
        $em->persist($idee);
        $em->flush();
        return $idee;
    }

    /**
     * Reset the counter (useful for test isolation)
     */
    public static function resetCounter(): void
    {
        self::$counter = 0;
    }
}
