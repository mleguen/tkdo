<?php

declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\Exclusion;
use App\Dom\Model\Utilisateur;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="tkdo_exclusion")
 */
class ExclusionAdaptor implements Exclusion
{
    /**
     * @var Utilisateur
     * @Id
     * @ManyToOne(targetEntity="App\Appli\ModelAdaptor\UtilisateurAdaptor")
     */
    private $quiOffre;

    /**
     * @var Utilisateur
     * @Id
     * @ManyToOne(targetEntity="App\Appli\ModelAdaptor\UtilisateurAdaptor")
     */
    private $quiNeDoitPasRecevoir;

    public function __construct(?Utilisateur $quiOffre = NULL, ?Utilisateur $quiNeDoitPasRecevoir = NULL)
    {
        if (isset($quiOffre)) $this->quiOffre = $quiOffre;
        if (isset($quiNeDoitPasRecevoir)) $this->quiNeDoitPasRecevoir = $quiNeDoitPasRecevoir;
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
    public function getQuiNeDoitPasRecevoir(): Utilisateur
    {
        return $this->quiNeDoitPasRecevoir;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuiOffre(Utilisateur $quiOffre): Exclusion
    {
        $this->quiOffre = $quiOffre;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuiNeDoitPasRecevoir(Utilisateur $quiNeDoitPasRecevoir): Exclusion
    {
        $this->quiNeDoitPasRecevoir = $quiNeDoitPasRecevoir;
        return $this;
    }
}
