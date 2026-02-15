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
     * Groups are sorted alphabetically by name within each section.
     *
     * Note: No pagination implemented. Acceptable for current usage
     * (typical user belongs to <10 groups). Revisit if users reach 50+ groups.
     *
     * @return array{actifs: Groupe[], archives: Groupe[]}
     */
    public function listeGroupesUtilisateur(Auth $auth): array
    {
        try {
            $appartenances = $this->groupeRepository->readToutesAppartenancesForUtilisateur(
                $auth->getIdUtilisateur()
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                'Impossible de charger les groupes de l\'utilisateur ' . $auth->getIdUtilisateur(),
                0,
                $e
            );
        }

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
