<?php

declare(strict_types=1);

namespace App\Dom\Port;

use App\Dom\Exception\PrefNotifIdeesPasPeriodiqueException;
use App\Dom\Model\Auth;
use App\Dom\Model\Idee;
use App\Dom\Model\PrefNotifIdees;
use App\Dom\Plugin\MailPlugin;
use App\Dom\Repository\IdeeRepository;
use App\Dom\Repository\UtilisateurRepository;
use DateTime;

class NotifPort
{
    private $ideeRepository;
    private $mailPlugin;
    private $utilisateurRepository;

    public function __construct(
        IdeeRepository $ideeRepository,
        MailPlugin $mailPlugin,
        UtilisateurRepository $utilisateurRepository
    ) {
        $this->ideeRepository = $ideeRepository;
        $this->mailPlugin = $mailPlugin;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    private function _getDebutPeriode(
        DateTime $dateNotif,
        string $periode
    ) {
        $debutPeriode = clone $dateNotif;
        switch ($periode) {
            case PrefNotifIdees::Quotidienne:
                $debutPeriode->setTime(0, 0, 0);
                break;

            default:
                throw new PrefNotifIdeesPasPeriodiqueException();
        }
        return $debutPeriode;
    }

    public function envoieNotifsInstantaneesCreation(
        Auth $auth,
        Idee $idee
    ): void {
        $this->envoieNotifsInstantanees($auth, $idee, [$this->mailPlugin, 'envoieMailIdeeCreation']);
    }

    public function envoieNotifsInstantaneesSuppression(
        Auth $auth,
        Idee $idee
    ): void {
        $this->envoieNotifsInstantanees($auth, $idee, [$this->mailPlugin, 'envoieMailIdeeSuppression']);
    }

    private function envoieNotifsInstantanees(
        Auth $auth,
        Idee $idee,
        callable $envoieMailIdee
    ): void {
        $utilisateursANotifier = $this->utilisateurRepository->readAllByNotifInstantaneePourIdees($idee->getUtilisateur());
        foreach ($utilisateursANotifier as $utilisateurANotifier) {
            if (!$auth->estUtilisateur($utilisateurANotifier)) {
                call_user_func($envoieMailIdee, $utilisateurANotifier, $idee);
            }
        }
    }

    public function envoieNotifsPeriodiques(
        string $periode,
        callable $avantEnvoiMail = null
    ): void {
        $dateNotif = new DateTime();
        $dateDebutPeriode = $this->_getDebutPeriode($dateNotif, $periode);
        $utilisateursANotifier = $this->utilisateurRepository->readAllByNotifPeriodique($periode, $dateDebutPeriode);
        foreach ($utilisateursANotifier as $utilisateurANotifier) {
            $idees = $this->ideeRepository->readAllByNotifPeriodique($utilisateurANotifier, $dateNotif);

            if ($avantEnvoiMail) call_user_func($avantEnvoiMail, $utilisateurANotifier, $idees);

            if ($this->mailPlugin->envoieMailNotifPeriodique(
                $utilisateurANotifier,
                $idees
            )) {
                $utilisateurANotifier->setDateDerniereNotifPeriodique($dateNotif);
                $this->utilisateurRepository->update($utilisateurANotifier);
            };
        }
    }
}
