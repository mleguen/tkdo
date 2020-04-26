<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

class Utilisateur extends UtilisateurSansMdp
{
    /**
     * @var string
     */
    private $mdp;

    /**
     * @param int|null  $id
     * @param string    $identifiant
     * @param string    $mdp
     * @param string    $nom
     */
    public function __construct(?int $id, string $identifiant, string $mdp, string $nom)
    {
        parent::__construct($id, $identifiant, $nom);
        $this->mdp = $mdp;
    }

    /**
     * @return string
     */
    public function getMdp(): string
    {
        return $this->mdp;
    }

    /**
     * @param string    $mdp
     */
    public function setMdp(string $mdp)
    {
        $this->mdp = $mdp;
    }

    /**
     * @return array
     */
    public function getUtilisateurSansMdp(): UtilisateurSansMdp
    {
        return new UtilisateurSansMdp($this->id, $this->identifiant, $this->nom);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'mdp' => $this->mdp,
        ]);
    }
}
