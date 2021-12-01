<?php

declare(strict_types=1);

namespace App\Dom\Port;

use App\Dom\Exception\OccasionPasseeException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\PasParticipantException;
use App\Dom\Exception\PasParticipantNiAdminException;
use App\Dom\Exception\PasUtilisateurNiAdminException;
use App\Dom\Exception\TirageDejaLanceException;
use App\Dom\Exception\TirageEchoueException;
use App\Dom\Exception\UtilisateurDejaParticipantException;
use App\Dom\Exception\UtilisateurOffreOuRecoitDejaException;
use App\Dom\Model\Auth;
use App\Dom\Model\Occasion;
use App\Dom\Model\Resultat;
use App\Dom\Model\Utilisateur;
use App\Dom\Plugin\MailPlugin;
use App\Dom\Repository\ExclusionRepository;
use App\Dom\Repository\OccasionRepository;
use App\Dom\Repository\ResultatRepository;
use App\Infra\Tools\ArrayTools;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class OccasionPort
{
    private $mailPlugin;
    private $exclusionRepository;
    private $occasionRepository;
    private $resultatRepository;

    public function __construct(
        MailPlugin $mailPlugin,
        ExclusionRepository $exclusionRepository,
        OccasionRepository $occasionRepository,
        ResultatRepository $resultatRepository
    ) {
        $this->mailPlugin = $mailPlugin;
        $this->exclusionRepository = $exclusionRepository;
        $this->occasionRepository = $occasionRepository;
        $this->resultatRepository = $resultatRepository;
    }

    /**
     * @throws PasAdminException
     * @throws UtilisateurDejaParticipantException
     */
    public function ajouteParticipantOccasion(
        Auth $auth,
        Occasion $occasion,
        Utilisateur $participant
    ): Occasion {
        if (!$auth->estAdmin()) throw new PasAdminException();

        $occasion->addParticipant($participant);

        try {
            $occasion = $this->occasionRepository->update($occasion);
        } catch (UniqueConstraintViolationException $err) {
            throw new UtilisateurDejaParticipantException();
        }

        if ($occasion->getDate() > new DateTime()) {
            $this->mailPlugin->envoieMailAjoutParticipant($participant, $occasion);
        }

        return $occasion;
    }

    /**
     * @throws PasAdminException
     * @throws PasParticipantException
     * @throws UtilisateurOffreOuRecoitDejaException
     */
    public function ajouteResultatOccasion(
        Auth $auth,
        Occasion $occasion,
        Utilisateur $quiOffre,
        Utilisateur $quiRecoit
    ): Resultat {
        if (!$auth->estAdmin()) throw new PasAdminException();

        $participants = $occasion->getParticipants();
        if (
            !ArrayTools::some($participants, [$quiOffre, 'estUtilisateur']) ||
            !ArrayTools::some($participants, [$quiRecoit, 'estUtilisateur'])
        ) {
            throw new PasParticipantException();
        }

        try {
            $resultat = $this->resultatRepository->create($occasion, $quiOffre, $quiRecoit);
        } catch (UniqueConstraintViolationException $err) {
            throw new UtilisateurOffreOuRecoitDejaException();
        }

        if ($occasion->getDate() > new DateTime()) {
            $this->mailPlugin->envoieMailTirageFait($quiOffre, $occasion);
        }

        return $resultat;
    }

    /**
     * @throws PasAdminException
     */
    public function creeOccasion(
        Auth $auth,
        DateTime $date,
        string $titre
    ): Occasion {
        if (!$auth->estAdmin()) throw new PasAdminException();

        $occasion = $this->occasionRepository->create(
            $date,
            $titre
        );

        return $occasion;
    }

    /**
     * @param Resultat[] $resultats
     * @throws PasParticipantNiAdminException
     */
    public function getOccasion(
        Auth $auth,
        Occasion $occasion,
        array &$resultats = null
    ): Occasion {
        if (
            !$auth->estAdmin() &&
            !ArrayTools::some($occasion->getParticipants(), [$auth, 'estUtilisateur'])
        ) {
            throw new PasParticipantNiAdminException();
        }
        $resultats = array_values(array_filter(
            $this->resultatRepository->readByOccasion($occasion),
            function (Resultat $resultat) use ($auth) {
                return $auth->estUtilisateur($resultat->getQuiOffre());
            }
        ));
        return $occasion;
    }

    /**
     * @throws PasAdminException
     * @throws TirageDejaLanceException
     * @throws TirageEchoueException
     * @return Resultat[]
     */
    public function lanceTirage(
        Auth $auth,
        Occasion $occasion,
        bool $force,
        ?int $nbMaxIter
    ): array {
        if (!$auth->estAdmin()) throw new PasAdminException();

        if ($occasion->getDate() <= new DateTime()) throw new OccasionPasseeException();

        if ($this->resultatRepository->hasForOccasion($occasion)) {
            if (!$force) throw new TirageDejaLanceException();
            $this->resultatRepository->deleteByOccasion($occasion);
        }

        $participants = $occasion->getParticipants();
        $tabExclusions = array_fill(0, count($participants), []);

        $exclusions = $this->exclusionRepository->readByParticipantsOccasion($occasion);
        foreach ($exclusions as $exclusion) {
            $idxQuiOffre = array_search($exclusion->getQuiOffre(), $participants);
            $idxQuiNeDoitPasRecevoir = array_search($exclusion->getQuiNeDoitPasRecevoir(), $participants);
            $tabExclusions[$idxQuiOffre][] = $idxQuiNeDoitPasRecevoir;
        }

        $resultatsPasses = $this->resultatRepository->readByParticipantsOccasion($occasion);
        foreach ($resultatsPasses as $resultatPasse) {
            $idxQuiOffre = array_search($resultatPasse->getQuiOffre(), $participants);
            $idxQuiNeDoitPasRecevoir = array_search($resultatPasse->getQuiRecoit(), $participants);
            $tabExclusions[$idxQuiOffre][] = $idxQuiNeDoitPasRecevoir;
        }

        $tabResultats = false;
        for ($iter = 0; !$tabResultats && $iter < ($nbMaxIter | 10); $iter++) {
            $tabResultats = $this->lanceTirage_(count($participants), $tabExclusions);
        }
        if (!$tabResultats) throw new TirageEchoueException();

        $resultats = [];
        foreach ($tabResultats as $idxQuiOffre => $idxQuiRecoit) {
            $quiOffre = $participants[$idxQuiOffre];
            $quiRecoit = $participants[$idxQuiRecoit];
            $resultats[] = $this->resultatRepository->create($occasion, $quiOffre, $quiRecoit);
        }

        foreach ($participants as $participant) {
            $this->mailPlugin->envoieMailTirageFait($participant, $occasion);
        }

        return $resultats;
    }

    /**
     * @param int[][] $tabExclusions
     * @return int[]|false
     */
    private function lanceTirage_(int $nbParticipants, array $tabExclusions)
    {
        srand();

        $dejaTire = array_fill(0, $nbParticipants, false);
        $tabResultats = array_fill(0, $nbParticipants, 0);

        for ($quiOffre = 0; $quiOffre < $nbParticipants; $quiOffre++) {
            $tabPossibilites = [];
            for ($quiRecoit = 0; $quiRecoit < $nbParticipants; $quiRecoit++) {
                if ($quiRecoit != $quiOffre && !$dejaTire[$quiRecoit] && !in_array($quiRecoit, $tabExclusions[$quiOffre])) {
                    $tabPossibilites[] = $quiRecoit;
                }
            }

            if (count($tabPossibilites) == 0) return false;

            $quiRecoit = $tabPossibilites[rand(0, count($tabPossibilites) - 1)];
            $tabResultats[$quiOffre] = $quiRecoit;
            $dejaTire[$quiRecoit] = true;
        }

        return $tabResultats;
    }

    /**
     * @return Occasion[]
     * @throws PasAdminException quand l'utilisateur n'est pas admin
     */
    public function listeOccasions(
        Auth $auth
    ): array {
        if (!$auth->estAdmin()) throw new PasAdminException();
        return $this->occasionRepository->readAll();
    }

    /**
     * @return Occasion[]
     * @throws PasUtilisateurNiAdminException quand un utilisateur non admin
     * tente de récupérer les occasions pour un autre participant que lui-même
     */
    public function listeOccasionsParticipant(
        Auth $auth,
        Utilisateur $participant
    ): array {
        if (!$auth->estUtilisateur($participant) && !$auth->estAdmin()) throw new PasUtilisateurNiAdminException();
        return $this->occasionRepository->readByParticipant($participant);
    }

    /**
     * @throws PasAdminException
     */
    public function modifieOccasion(
        Auth $auth,
        Occasion $occasion,
        array $modifications
    ): Occasion {
        if (!$auth->estAdmin()) throw new PasAdminException();

        if (isset($modifications['date'])) $occasion->setDate($modifications['date']);
        if (isset($modifications['titre'])) $occasion->setTitre($modifications['titre']);

        return $this->occasionRepository->update($occasion);
    }
}
