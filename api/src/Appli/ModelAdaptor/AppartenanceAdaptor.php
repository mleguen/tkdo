<?php

declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\Appartenance;
use App\Dom\Model\Groupe;
use App\Dom\Model\Utilisateur;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="tkdo_groupe_utilisateur")
 */
class AppartenanceAdaptor implements Appartenance
{
    /**
     * @Id
     * @ManyToOne(targetEntity="App\Appli\ModelAdaptor\GroupeAdaptor", inversedBy="appartenances")
     */
    private Groupe $groupe;

    /**
     * @Id
     * @ManyToOne(targetEntity="App\Appli\ModelAdaptor\UtilisateurAdaptor")
     */
    private Utilisateur $utilisateur;

    /**
     * @Column(type="boolean", name="est_admin")
     */
    private bool $estAdmin = false;

    /**
     * @Column(type="datetime", name="date_ajout")
     */
    private DateTime $dateAjout;

    public function __construct(?Groupe $groupe = NULL, ?Utilisateur $utilisateur = NULL)
    {
        if (isset($groupe)) $this->groupe = $groupe;
        if (isset($utilisateur)) $this->utilisateur = $utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupe(): Groupe
    {
        return $this->groupe;
    }

    /**
     * {@inheritdoc}
     */
    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function getEstAdmin(): bool
    {
        return $this->estAdmin;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateAjout(): DateTime
    {
        return $this->dateAjout;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroupe(Groupe $groupe): Appartenance
    {
        $this->groupe = $groupe;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUtilisateur(Utilisateur $utilisateur): Appartenance
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEstAdmin(bool $estAdmin): Appartenance
    {
        $this->estAdmin = $estAdmin;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDateAjout(DateTime $dateAjout): Appartenance
    {
        $this->dateAjout = $dateAjout;
        return $this;
    }
}
