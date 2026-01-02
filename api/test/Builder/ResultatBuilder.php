<?php

declare(strict_types=1);

namespace Test\Builder;

use App\Appli\ModelAdaptor\OccasionAdaptor;
use App\Appli\ModelAdaptor\ResultatAdaptor;
use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use Doctrine\ORM\EntityManager;

/**
 * Fluent builder for creating test Resultat entities
 *
 * Usage:
 *   $resultat = ResultatBuilder::aResultat()
 *       ->forOccasion($occasion)
 *       ->withQuiOffre($giver)
 *       ->withQuiRecoit($receiver)
 *       ->build();
 *
 *   $resultat = ResultatBuilder::aResultat()
 *       ->forOccasion($occasion)
 *       ->withQuiOffre($user1)
 *       ->withQuiRecoit($user2)
 *       ->persist($em);
 */
class ResultatBuilder
{
    private ?OccasionAdaptor $occasion = null;
    private ?UtilisateurAdaptor $quiOffre = null;
    private ?UtilisateurAdaptor $quiRecoit = null;

    private function __construct()
    {
    }

    /**
     * Create a new ResultatBuilder
     */
    public static function aResultat(): self
    {
        return new self();
    }

    /**
     * Set the occasion for this result
     */
    public function forOccasion(OccasionAdaptor $occasion): self
    {
        $this->occasion = $occasion;
        return $this;
    }

    /**
     * Set who gives the gift (qui offre)
     */
    public function withQuiOffre(UtilisateurAdaptor $quiOffre): self
    {
        $this->quiOffre = $quiOffre;
        return $this;
    }

    /**
     * Set who receives the gift (qui recoit)
     */
    public function withQuiRecoit(UtilisateurAdaptor $quiRecoit): self
    {
        $this->quiRecoit = $quiRecoit;
        return $this;
    }

    /**
     * Build the Resultat entity in memory (not persisted)
     *
     * All three fields (occasion, quiOffre, quiRecoit) are required.
     */
    public function build(): ResultatAdaptor
    {
        if ($this->occasion === null) {
            throw new \InvalidArgumentException('Occasion is required for ResultatBuilder');
        }
        if ($this->quiOffre === null) {
            throw new \InvalidArgumentException('QuiOffre is required for ResultatBuilder');
        }
        if ($this->quiRecoit === null) {
            throw new \InvalidArgumentException('QuiRecoit is required for ResultatBuilder');
        }

        $resultat = new ResultatAdaptor();
        $resultat
            ->setOccasion($this->occasion)
            ->setQuiOffre($this->quiOffre)
            ->setQuiRecoit($this->quiRecoit);

        return $resultat;
    }

    /**
     * Build and persist the Resultat entity to the database
     *
     * Note: This will also persist occasion, quiOffre, and quiRecoit if they are not yet persisted.
     */
    public function persist(EntityManager $em): ResultatAdaptor
    {
        // Ensure related entities exist in database
        if ($this->occasion !== null && !$em->contains($this->occasion)) {
            $em->persist($this->occasion);
        }
        if ($this->quiOffre !== null && !$em->contains($this->quiOffre)) {
            $em->persist($this->quiOffre);
        }
        if ($this->quiRecoit !== null && !$em->contains($this->quiRecoit)) {
            $em->persist($this->quiRecoit);
        }

        $resultat = $this->build();
        $em->persist($resultat);
        $em->flush();
        return $resultat;
    }
}
