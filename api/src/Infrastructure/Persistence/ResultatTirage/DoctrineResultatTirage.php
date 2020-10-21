<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\ResultatTirage;

use App\Domain\Occasion\Occasion;
use App\Domain\ResultatTirage\ResultatTirage;
use App\Domain\Utilisateur\Utilisateur;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @Entity
 * @Table(
 *      name="tkdo_resultat_tirage",
 *      uniqueConstraints={
 *          @UniqueConstraint(name="unique_quiRecoit_id_idx", columns={"occasion_id", "quiRecoit_id"})
 *      }
 * )
 */
class DoctrineResultatTirage implements ResultatTirage
{
    /**
     * @var Occasion
     * @Id
     * @ManyToOne(targetEntity="App\Infrastructure\Persistence\Occasion\DoctrineOccasion")
     */
    private $occasion;

    /**
     * @var Utilisateur
     * @Id
     * @ManyToOne(targetEntity="App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur")
     */
    private $quiOffre;

    /**
     * @var Utilisateur
     * @ManyToOne(targetEntity="App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur")
     * @JoinColumn(nullable=false)
     */
    private $quiRecoit;

    public function __construct(Occasion $occasion= NULL, Utilisateur $quiOffre = NULL)
    {
        if (isset($occasion)) $this->occasion = $occasion;
        if (isset($quiOffre)) $this->quiOffre = $quiOffre;
    }

    public function getOccasion(): Occasion
    {
        return $this->occasion;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuiOffre(): Utilisateur
    {
        return $this->quiOffre;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuiRecoit(): Utilisateur
    {
        return $this->quiRecoit;
    }

    /**
     * {@inheritdoc}
     */
    public function setOccasion(Occasion $occasion): ResultatTirage
    {
        $this->occasion = $occasion;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuiOffre(Utilisateur $quiOffre): ResultatTirage
    {
        $this->quiOffre = $quiOffre;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuiRecoit(Utilisateur $quiRecoit): ResultatTirage
    {
        $this->quiRecoit = $quiRecoit;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->occasion = clone $this->occasion;
        $this->quiOffre = clone $this->quiOffre;
        $this->quiRecoit = clone $this->quiRecoit;
    }
}