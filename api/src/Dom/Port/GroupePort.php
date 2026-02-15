<?php
declare(strict_types=1);

namespace App\Dom\Port;

use App\Dom\Model\Auth;
use App\Dom\Model\Groupe;
use App\Dom\Repository\GroupeRepository;

class GroupePort
{
    public function __construct(
        private readonly GroupeRepository $groupeRepository
    ) {
    }

    /**
     * Returns user's groups separated into active and archived.
     *
     * @return array{actifs: Groupe[], archives: Groupe[]}
     */
    public function listeGroupesUtilisateur(Auth $auth): array
    {
        $appartenances = $this->groupeRepository->readToutesAppartenancesForUtilisateur(
            $auth->getIdUtilisateur()
        );

        $actifs = [];
        $archives = [];
        foreach ($appartenances as $appartenance) {
            $groupe = $appartenance->getGroupe();
            if ($groupe->getArchive()) {
                $archives[] = $groupe;
            } else {
                $actifs[] = $groupe;
            }
        }

        return ['actifs' => $actifs, 'archives' => $archives];
    }
}
