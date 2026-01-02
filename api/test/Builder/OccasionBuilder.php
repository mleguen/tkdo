<?php

declare(strict_types=1);

namespace Test\Builder;

use App\Appli\ModelAdaptor\OccasionAdaptor;
use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use DateTime;
use Doctrine\ORM\EntityManager;

/**
 * Fluent builder for creating test Occasion entities
 *
 * Note: The static counter is not thread-safe. PHPUnit runs tests sequentially
 * by default, so this is not an issue for current test execution.
 *
 * Usage:
 *   $occasion = OccasionBuilder::anOccasion()
 *       ->withTitre('Noël 2024')
 *       ->withDate(new DateTime('2024-12-25'))
 *       ->build();
 *
 *   $occasion = OccasionBuilder::anOccasion()
 *       ->withParticipants([$user1, $user2])
 *       ->persist($em);
 */
class OccasionBuilder
{
    private static int $counter = 0;

    private string $titre;
    private DateTime $date;
    /** @var UtilisateurAdaptor[] */
    private array $participants = [];

    private function __construct()
    {
        self::$counter++;
        $this->titre = 'Occasion ' . self::$counter;
        $this->date = new DateTime('tomorrow');
    }

    /**
     * Create a new OccasionBuilder with default values
     */
    public static function anOccasion(): self
    {
        return new self();
    }

    /**
     * Set the titre (title/name)
     */
    public function withTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    /**
     * Set the date
     */
    public function withDate(DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Set the participants
     *
     * @param UtilisateurAdaptor[] $participants
     */
    public function withParticipants(array $participants): self
    {
        $this->participants = $participants;
        return $this;
    }

    /**
     * Add a single participant
     */
    public function withParticipant(UtilisateurAdaptor $participant): self
    {
        $this->participants[] = $participant;
        return $this;
    }

    /**
     * Build the Occasion entity in memory (not persisted)
     */
    public function build(): OccasionAdaptor
    {
        $occasion = new OccasionAdaptor();
        $occasion
            ->setTitre($this->titre)
            ->setDate($this->date)
            ->setParticipants($this->participants);

        return $occasion;
    }

    /**
     * Build and persist the Occasion entity to the database
     */
    public function persist(EntityManager $em): OccasionAdaptor
    {
        $occasion = $this->build();
        $em->persist($occasion);
        $em->flush();
        return $occasion;
    }

    /**
     * Reset the counter (useful for test isolation)
     */
    public static function resetCounter(): void
    {
        self::$counter = 0;
    }
}
