<?php

declare(strict_types=1);

namespace App\Dom\Plugin;

use App\Dom\Model\Idee;
use App\Dom\Model\Occasion;
use App\Dom\Model\Utilisateur;

interface MailPlugin
{
    public function envoieMailAjoutParticipant(
        Utilisateur $destinataire,
        Occasion $occasion
    ): bool;

    public function envoieMailIdeeCreation(
        Utilisateur $destinataire,
        Idee $idee
    ): bool;

    public function envoieMailIdeeSuppression(
        Utilisateur $destinataire,
        Idee $idee
    ): bool;

    public function envoieMailMdpCreation(
        Utilisateur $destinataire,
        string $mdp
    ): bool;

    public function envoieMailMdpReinitialisation(
        Utilisateur $destinataire,
        string $mdp
    ): bool;

    /**
     * @param Idee[] $idees
     */
    public function envoieMailNotifPeriodique(
        Utilisateur $destinataire,
        array $idees
    ): bool;

    public function envoieMailTirageFait(
        Utilisateur $destinataire,
        Occasion $occasion
    ): bool;
}
