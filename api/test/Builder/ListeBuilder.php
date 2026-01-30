<?php

declare(strict_types=1);

namespace Test\Builder;

/**
 * Fluent builder for creating test Liste entities
 *
 * SCAFFOLD: This builder is a placeholder for v2 Liste entity support.
 * The actual ListeAdaptor entity will be created in a later story.
 * Update this builder when the entity is implemented.
 *
 * Liste represents the visibility assignment of a user's idea list to groups.
 * It controls which groups can see a user's ideas.
 *
 * Note: The static counter is not thread-safe. PHPUnit runs tests sequentially
 * by default, so this is not an issue for current test execution.
 *
 * Planned usage:
 *   $liste = ListeBuilder::uneListe()
 *       ->forUtilisateur($user)
 *       ->forGroupe($groupe)
 *       ->build();
 *
 *   $liste = ListeBuilder::uneListe()
 *       ->forUtilisateur($user)
 *       ->forGroupe($groupe)
 *       ->withVisibilite(true)
 *       ->persist($em);
 */
class ListeBuilder
{
    private static int $counter = 0;

    /** @var object|null User whose list this is (UtilisateurAdaptor) */
    private ?object $utilisateur = null;
    /** @var object|null Group that can see the list (GroupeAdaptor) */
    private ?object $groupe = null;
    private bool $visible = true;

    private function __construct()
    {
        self::$counter++;
    }

    /**
     * Create a new ListeBuilder with default values
     */
    public static function uneListe(): self
    {
        return new self();
    }

    /**
     * Set the user whose list this is
     *
     * @param object $utilisateur UtilisateurAdaptor entity
     */
    public function forUtilisateur(object $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    /**
     * Set the group that can see the list
     *
     * @param object $groupe GroupeAdaptor entity
     */
    public function forGroupe(object $groupe): self
    {
        $this->groupe = $groupe;
        return $this;
    }

    /**
     * Set whether the list is visible to the group
     */
    public function withVisibilite(bool $visible): self
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * Make the list visible
     */
    public function visible(): self
    {
        $this->visible = true;
        return $this;
    }

    /**
     * Make the list hidden
     */
    public function cachee(): self
    {
        $this->visible = false;
        return $this;
    }

    /**
     * Build the Liste entity in memory (not persisted)
     *
     * TODO: Implement when ListeAdaptor entity is created
     *
     * @return object Placeholder - will return ListeAdaptor
     */
    public function build(): object
    {
        if ($this->utilisateur === null) {
            throw new \InvalidArgumentException('Utilisateur is required for ListeBuilder');
        }
        if ($this->groupe === null) {
            throw new \InvalidArgumentException('Groupe is required for ListeBuilder');
        }

        // TODO: Replace with actual implementation when ListeAdaptor exists
        // $liste = new ListeAdaptor();
        // $liste
        //     ->setUtilisateur($this->utilisateur)
        //     ->setGroupe($this->groupe)
        //     ->setVisible($this->visible);
        //
        // return $liste;

        throw new \RuntimeException(
            'ListeBuilder::build() is a scaffold. ' .
            'Implement when ListeAdaptor entity is created'
        );
    }

    /**
     * Build and persist the Liste entity to the database
     *
     * TODO: Implement when ListeAdaptor entity is created
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @return object Placeholder - will return ListeAdaptor
     */
    public function persist(\Doctrine\ORM\EntityManager $em): object
    {
        // TODO: Replace with actual implementation when ListeAdaptor exists
        // if ($this->utilisateur !== null && !$em->contains($this->utilisateur)) {
        //     $em->persist($this->utilisateur);
        // }
        // if ($this->groupe !== null && !$em->contains($this->groupe)) {
        //     $em->persist($this->groupe);
        // }
        //
        // $liste = $this->build();
        // $em->persist($liste);
        // $em->flush();
        // return $liste;

        throw new \RuntimeException(
            'ListeBuilder::persist() is a scaffold. ' .
            'Implement when ListeAdaptor entity is created'
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
     * @return array{utilisateur: ?object, groupe: ?object, visible: bool}
     */
    public function getValues(): array
    {
        return [
            'utilisateur' => $this->utilisateur,
            'groupe' => $this->groupe,
            'visible' => $this->visible,
        ];
    }
}
