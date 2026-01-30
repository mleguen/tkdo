<?php

declare(strict_types=1);

namespace Test\Builder;

/**
 * Fluent builder for creating test Groupe entities
 *
 * SCAFFOLD: This builder is a placeholder for v2 Groupe entity support.
 * The actual GroupeAdaptor entity will be created in Story 2.1.
 * Update this builder when the entity is implemented.
 *
 * Note: The static counter is not thread-safe. PHPUnit runs tests sequentially
 * by default, so this is not an issue for current test execution.
 *
 * Planned usage:
 *   $groupe = GroupeBuilder::unGroupe()
 *       ->withNom('Famille Dupont')
 *       ->withDescription('Groupe familial')
 *       ->build();
 *
 *   $groupe = GroupeBuilder::unGroupe()
 *       ->withNom('Amis')
 *       ->withMembres([$user1, $user2])
 *       ->persist($em);
 */
class GroupeBuilder
{
    private static int $counter = 0;

    private string $nom;
    private ?string $description = null;
    /** @var array<object> List of member entities (UtilisateurAdaptor) */
    private array $membres = [];

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
     * Set the group description
     */
    public function withDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the group members
     *
     * @param array<object> $membres List of UtilisateurAdaptor entities
     */
    public function withMembres(array $membres): self
    {
        $this->membres = $membres;
        return $this;
    }

    /**
     * Add a member to the group
     *
     * @param object $membre UtilisateurAdaptor entity
     */
    public function addMembre(object $membre): self
    {
        $this->membres[] = $membre;
        return $this;
    }

    /**
     * Build the Groupe entity in memory (not persisted)
     *
     * TODO: Implement when GroupeAdaptor entity is created in Story 2.1
     *
     * @return object Placeholder - will return GroupeAdaptor
     */
    public function build(): object
    {
        // TODO: Replace with actual implementation when GroupeAdaptor exists
        // $groupe = new GroupeAdaptor();
        // $groupe
        //     ->setNom($this->nom)
        //     ->setDescription($this->description);
        //
        // foreach ($this->membres as $membre) {
        //     $groupe->addMembre($membre);
        // }
        //
        // return $groupe;

        throw new \RuntimeException(
            'GroupeBuilder::build() is a scaffold. ' .
            'Implement when GroupeAdaptor entity is created in Story 2.1'
        );
    }

    /**
     * Build and persist the Groupe entity to the database
     *
     * TODO: Implement when GroupeAdaptor entity is created in Story 2.1
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @return object Placeholder - will return GroupeAdaptor
     */
    public function persist(\Doctrine\ORM\EntityManager $em): object
    {
        // TODO: Replace with actual implementation when GroupeAdaptor exists
        // foreach ($this->membres as $membre) {
        //     if (!$em->contains($membre)) {
        //         $em->persist($membre);
        //     }
        // }
        //
        // $groupe = $this->build();
        // $em->persist($groupe);
        // $em->flush();
        // return $groupe;

        throw new \RuntimeException(
            'GroupeBuilder::persist() is a scaffold. ' .
            'Implement when GroupeAdaptor entity is created in Story 2.1'
        );
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
     * @return array{nom: string, description: ?string, membres: array<object>}
     */
    public function getValues(): array
    {
        return [
            'nom' => $this->nom,
            'description' => $this->description,
            'membres' => $this->membres,
        ];
    }
}
