<?php

declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\Occasion;
use App\Dom\Model\Resultat;
use App\Dom\Model\Utilisateur;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @Entity
 * @Table(
 *      name="tkdo_resultat",
 *      uniqueConstraints={
 *          @UniqueConstraint(name="unique_quiRecoit_id_idx", columns={"occasion_id", "quiRecoit_id"})
 *      }
 * )
 */
class ResultatAdaptor implements Resultat
{
    /**
     * @Id
     * @ManyToOne(targetEntity="App\Appli\ModelAdaptor\OccasionAdaptor")
     */
    private Occasion $occasion;

    /**
     * @Id
     * @ManyToOne(targetEntity="App\Appli\ModelAdaptor\UtilisateurAdaptor")
     */
    private Utilisateur $quiOffre;

    /**
     * @ManyToOne(targetEntity="App\Appli\ModelAdaptor\UtilisateurAdaptor")
     * @JoinColumn(nullable=false)
     */
    private Utilisateur $quiRecoit;

    public function __construct(?Occasion $occasion= NULL, ?Utilisateur $quiOffre = NULL)
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
    public function setOccasion(Occasion $occasion): Resultat
    {
        $this->occasion = $occasion;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuiOffre(Utilisateur $quiOffre): Resultat
    {
        $this->quiOffre = $quiOffre;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuiRecoit(Utilisateur $quiRecoit): Resultat
    {
        $this->quiRecoit = $quiRecoit;
        return $this;
    }
}
