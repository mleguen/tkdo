<?php

declare(strict_types=1);

namespace App\Appli\PluginAdaptor;

use App\Appli\Service\MailService;
use App\Appli\Service\UriService;
use App\Appli\Settings\MailSettings;
use App\Dom\Model\Idee;
use App\Dom\Model\Occasion;
use App\Dom\Model\Utilisateur;
use App\Dom\Plugin\MailPlugin;
use stdClass;

class MailPluginAdaptor implements MailPlugin
{
    const SIGNATURE = <<<EOS
Cordialement,
Votre administrateur Tkdo.
EOS;

    public function __construct(private readonly MailService $mailService, private readonly MailSettings $settings, private readonly UriService $uriService)
    {
    }
    
    public function envoieMailAjoutParticipant(
        Utilisateur $destinataire,
        Occasion $occasion
    ): bool
    {
        return $this->mailService->envoieMail(
            $destinataire->getEmail(),
            "Participation au tirage cadeaux {$occasion->getTitre()}",
            <<<EOS
Bonjour {$destinataire->getNom()},

Vous participez désormais au tirage cadeaux {$occasion->getTitre()}.

Pour découvrir les noms des autres participants,
et commencer à proposer des idées de cadeaux,
rendez-vous sur {$this->uriService->getUri("/occasion/{$occasion->getId()}")}

{$this->settings->signature}
EOS
        );
    }

    private function envoieMailIdee(
        Utilisateur $destinataire,
        Utilisateur $utilisateur,
        string $sujet,
        string $motif
    ): bool
    {
        return $this->mailService->envoieMail(
            $destinataire->getEmail(),
            $sujet,
            <<<EOS
Bonjour {$destinataire->getNom()},

$motif

Pour consulter la liste d'idées de {$utilisateur->getNom()},
rendez-vous sur {$this->uriService->getUri('/idee', "idUtilisateur={$utilisateur->getId()}")}

{$this->settings->signature}
EOS
        );
    }

    public function envoieMailIdeeCreation(
        Utilisateur $destinataire,
        Idee $idee
    ): bool
    {
        $utilisateur = $idee->getUtilisateur();
        return $this->envoieMailIdee(
            $destinataire,
            $utilisateur,
            "Nouvelle idée de cadeau pour {$utilisateur->getNom()}",
            "Une nouvelle idée de cadeau a été proposée pour {$utilisateur->getNom()} :\n\n  > {$idee->getDescription()}"
        );
    }

    public function envoieMailIdeeSuppression(
        Utilisateur $destinataire,
        Idee $idee
    ): bool
    {
        $utilisateur = $idee->getUtilisateur();
        return $this->envoieMailIdee(
            $destinataire,
            $utilisateur,
            "Idée de cadeau supprimée pour {$utilisateur->getNom()}",
            "L'idée de cadeau pour {$utilisateur->getNom()} ci-dessous a été retirée de sa liste :\n\n  > {$idee->getDescription()}"
        );
    }

    private function envoieMailMdp(
        Utilisateur $destinataire,
        string $mdp,
        string $sujet,
        string $motif
    ): bool
    {
        return $this->mailService->envoieMail(
            $destinataire->getEmail(),
            $sujet,
            <<<EOS
Bonjour {$destinataire->getNom()},

$motif.

Pour accéder à l'application, connectez vous à {$this->uriService->getUri()}
avec les identifiants suivants :
- identifiant : {$destinataire->getIdentifiant()}
- mot de passe : $mdp

{$this->settings->signature}
EOS
        );
    }

    public function envoieMailMdpCreation(
        Utilisateur $destinataire,
        string $mdp
    ): bool
    {
        return $this->envoieMailMdp(
            $destinataire,
            $mdp,
            'Création de votre compte',
            "Votre compte Tkdo (tirages cadeaux) vient d'être créé"
        );
    }

    public function envoieMailMdpReinitialisation(
        Utilisateur $destinataire,
        string $mdp
    ): bool
    {
        return $this->envoieMailMdp(
            $destinataire,
            $mdp,
            'Réinitialisation de votre mot de passe',
            'Le mot de passe de votre compte Tkdo (tirages cadeaux) a été réinitialisé'
        );
    }

    public function envoieMailNotifPeriodique(
        Utilisateur $destinataire,
        array $idees
    ): bool
    {
        if (count($idees) === 0) return true;

        $ideesUtilisateurs = [];
        foreach($idees as $idee) {
            $nomUtilisateur = $idee->getUtilisateur()->getNom();
            if (!isset($ideesUtilisateurs[$nomUtilisateur])) {
                $ideesUtilisateurs[$nomUtilisateur] = new stdClass();
                $ideesUtilisateurs[$nomUtilisateur]->id = $idee->getUtilisateur()->getId();
                $ideesUtilisateurs[$nomUtilisateur]->creations = [];
                $ideesUtilisateurs[$nomUtilisateur]->suppressions = [];
            }
            if ($idee->hasDateSuppression()) {
                $ideesUtilisateurs[$nomUtilisateur]->suppressions[] = $idee;
            } else {
                $ideesUtilisateurs[$nomUtilisateur]->creations[] = $idee;
            }
        }
        ksort($ideesUtilisateurs);

        $contenu = "";
        foreach($ideesUtilisateurs as $nomUtilisateur => $ideesUtilisateur) {

            if (count($ideesUtilisateur->creations) > 0) {
                if (count($ideesUtilisateur->creations) === 1) {
                    $contenu .= "\nUne nouvelle idée de cadeau a été proposée pour $nomUtilisateur :\n\n";
                } else {
                    $contenu .= "\nDe nouvelles idées de cadeaux ont été proposées pour $nomUtilisateur :\n\n";
                }
                foreach($ideesUtilisateur->creations as $idee) {
                    $contenu .= "  > {$idee->getDescription()}\n";
                }
            }

            if (count($ideesUtilisateur->suppressions) > 0) {
                if (count($ideesUtilisateur->suppressions) === 1) {
                    $contenu .= "\nL'idée de cadeau pour $nomUtilisateur ci-dessous a été retirée de sa liste :\n\n";
                } else {
                    $contenu .= "\nLes idées de cadeau pour $nomUtilisateur ci-dessous ont été retirées de sa liste :\n\n";
                }
                foreach($ideesUtilisateur->suppressions as $idee) {
                    $contenu .= "  > {$idee->getDescription()}\n";
                }
            }

            if ((count($ideesUtilisateur->creations) > 0) || (count($ideesUtilisateur->suppressions) > 0)) {
                $contenu .= "\nPour consulter la liste d'idées de $nomUtilisateur,\n";
                $contenu .= "rendez-vous sur {$this->uriService->getUri('/idee', "idUtilisateur={$ideesUtilisateur->id}")}\n";
            }
        }

        return $this->mailService->envoieMail(
            $destinataire->getEmail(),
            "Actualités Tkdo",
            <<<EOS
Bonjour {$destinataire->getNom()},
$contenu
{$this->settings->signature}
EOS
        );
    }

    public function envoieMailTirageFait(
        Utilisateur $destinataire,
        Occasion $occasion
    ): bool
    {
        return $this->mailService->envoieMail(
            $destinataire->getEmail(),
            "Tirage au sort fait pour {$occasion->getTitre()}",
            <<<EOS
Bonjour {$destinataire->getNom()},

Le tirage au sort est fait pour '{$occasion->getTitre()}' !

Pour découvrir à qui vous aurez le plaisir de faire un cadeau,
rendez-vous sur {$this->uriService->getUri("/occasion/{$occasion->getId()}")}

{$this->settings->signature}
EOS
        );
    }
}
