<?php

declare(strict_types=1);

namespace App\Dom\Port;

use App\Dom\Exception\IdeePasAuteurException;
use App\Dom\Exception\IdeeDejaSupprimeeException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Model\Idee;
use App\Dom\Repository\IdeeRepository;
use App\Dom\Model\Auth;
use App\Dom\Model\Utilisateur;
use DateTime;

class IdeePort
{
    private $ideeRepository;
    private $notifPort;

    public function __construct(
        IdeeRepository $ideeRepository,
        NotifPort $notifPort
    )
    {
        $this->ideeRepository = $ideeRepository;
        $this->notifPort = $notifPort;
    }

    /**
     * @throws IdeePasAuteurException quand l'utilisateur authentifié n'est ni l'auteur, ni un admin
     */
    public function creeIdee(
        Auth $auth,
        Utilisateur $utilisateur,
        string $description,
        Utilisateur $auteur
    ): Idee
    {
        if (!$auth->estUtilisateur($auteur) && !$auth->estAdmin()) throw new IdeePasAuteurException();

        $idee = $this->ideeRepository->create(
            $utilisateur,
            $description,
            $auteur,
            new DateTime()
        );

        $this->notifPort->envoieNotifsInstantaneesCreation($auth, $idee);

        return $idee;
    }

    /**
     * @return Idee[]
     * @throws PasAdminException quand un utilisateur non admin
     * tente de récupérer des idées supprimées
     */
    public function listeIdees(
        Auth $auth,
        Utilisateur $utilisateur,
        bool $supprimees = null
    ): array
    {
        if (($supprimees !== false) && !$auth->estAdmin()) throw new PasAdminException();

        return array_values(array_filter(
            $this->ideeRepository->readAllByUtilisateur($utilisateur, $supprimees),
            function (Idee $i) use ($auth) {
                return (
                    // L'utilisateur authentifié ne peut voir que les idées dont il est l'auteur
                    $auth->estUtilisateur($i->getAuteur()) ||
                    // ou qui ont été proposées pour quelqu'un d'autre que lui
                    !$auth->estUtilisateur($i->getUtilisateur())
                );
            }
        ));
    }

    /**
     * @throws IdeePasAuteurException quand l'utilisateur authentifié n'est ni l'auteur, ni un admin
     * @throws IdeeDejaSupprimeeException
     */
    public function marqueIdeeCommeSupprimee(
        Auth $auth,
        Idee $idee
    ): Idee
    {
        if (!$auth->estUtilisateur($idee->getAuteur()) && !$auth->estAdmin()) throw new IdeePasAuteurException();

        if ($idee->hasDateSuppression()) throw new IdeeDejaSupprimeeException();

        $idee->setDateSuppression(new DateTime());
        $this->ideeRepository->update($idee);

        $this->notifPort->envoieNotifsInstantaneesSuppression($auth, $idee);

        return $idee;
    }
}
