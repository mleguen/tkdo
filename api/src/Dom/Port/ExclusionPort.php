<?php

declare(strict_types=1);

namespace App\Dom\Port;

use App\Dom\Exception\DoublonExclusionException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Model\Auth;
use App\Dom\Model\Exclusion;
use App\Dom\Model\Utilisateur;
use App\Dom\Repository\ExclusionRepository;

class ExclusionPort
{
    public function __construct(private readonly ExclusionRepository $exclusionRepository)
    {
    }

    /**
     * @throws PasAdminException
     * @throws DoublonExclusionException
     */
    public function creeExclusion(
        Auth $auth,
        Utilisateur $quiOffre,
        Utilisateur $quiNeDoitPasrecevoir
    ): Exclusion
    {
        if (!$auth->estAdmin()) throw new PasAdminException();

        $exclusion = $this->exclusionRepository->create(
            $quiOffre,
            $quiNeDoitPasrecevoir
        );

        return $exclusion;
    }

    /**
     * @return Exclusion[]
     * @throws PasAdminException
     */
    public function listeExclusions(
        Auth $auth,
        Utilisateur $quiOffre
    ): array {
        if (!$auth->estAdmin()) throw new PasAdminException();
        return $this->exclusionRepository->readByQuiOffre($quiOffre);
    }
}
