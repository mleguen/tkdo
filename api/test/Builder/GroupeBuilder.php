<?php

declare(strict_types=1);

namespace Test\Builder;

use App\Appli\ModelAdaptor\GroupeAdaptor;
use DateTime;
use Doctrine\ORM\EntityManager;

/**
 * Fluent builder for creating test Groupe entities
 *
 * Note: The static counter is not thread-safe. PHPUnit runs tests sequentially
 * by default, so this is not an issue for current test execution.
 *
 * Usage:
 *   $groupe = GroupeBuilder::unGroupe()
 *       ->withNom('Famille Dupont')
 *       ->build();
 *
 *   $groupe = GroupeBuilder::unGroupe()
 *       ->withNom('Amis')
 *       ->persist($em);
 */
class GroupeBuilder
{
    private static int $counter = 0;

    private string $nom;
    private bool $archive = false;

    private function __construct()
    {
        self::$counter++;
        $this->nom = 'Groupe ' . self::$counter;
    }

    /**
     * Create a new GroupeBuilder with default values
     */
    public static function unGroupe(): self
    {
        return new self();
    }

    /**
     * Set the group name
     */
    public function withNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * Set the archive flag
     */
    public function withArchive(bool $archive): self
    {
        $this->archive = $archive;
        return $this;
    }

    /**
     * Build the Groupe entity in memory (not persisted)
     */
    public function build(): GroupeAdaptor
    {
        $groupe = new GroupeAdaptor();
        $groupe->setNom($this->nom)
            ->setArchive($this->archive)
            ->setDateCreation(new DateTime());
        return $groupe;
    }

    /**
     * Build and persist the Groupe entity to the database
     */
    public function persist(EntityManager $em): GroupeAdaptor
    {
        $groupe = $this->build();
        $em->persist($groupe);
        $em->flush();
        return $groupe;
    }

    /**
     * Reset the counter (useful for test isolation)
     */
    public static function resetCounter(): void
    {
        self::$counter = 0;
    }

    /**
     * Get the current values (for testing the builder itself)
     *
     * @return array{nom: string, archive: bool}
     */
    public function getValues(): array
    {
        return [
            'nom' => $this->nom,
            'archive' => $this->archive,
        ];
    }
}
