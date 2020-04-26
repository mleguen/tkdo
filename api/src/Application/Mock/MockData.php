<?php
declare(strict_types=1);

namespace App\Application\Mock;

use App\Domain\Utilisateur\UtilisateurRepository;

// TODO : finir de passer en repository
class MockData
{
    /**
     * @var UtilisateurRepository
     */
    protected $utilisateurRepository;

    /**
     * @param LoggerInterface $logger
     * @param UtilisateurRepository  $utilisateurRepository
     */
    public function __construct(UtilisateurRepository $utilisateurRepository)
    {
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function getToken(): string {
        return "fake-jwt-token";
    }

    public function getOccasion() {
        return [
            "titre" => 'Noël 2020',
            "participants" => [
                $this->utilisateurRepository->find(0),
                ["id" => 1, "nom" => 'Bob', "aQuiOffrir" => true],
                ["id" => 2, "nom" => 'Charlie'],
                ["id" => 3, "nom" => 'David'],
            ],
        ];
    }

    public function getListeIdees(int $idUtilisateur) {
        $nomAlice = $this->utilisateurRepository->find(0)->getNom();
        switch ($idUtilisateur) {
            case 0:
                return [
                    "nomUtilisateur" => $nomAlice,
                    "estMoi" => true,
                    "idees" => [
                        [ "id" => 0, "desc" => 'un gauffrier', "auteur" => $nomAlice, "date" => '19/04/2020', "estDeMoi" => true ],
                    ],
                ];
            case 1:
                return [
                    "nomUtilisateur" => 'Bob',
                    "idees" => [
                        [ "id" => 0, "desc" => 'une canne à pêche', "auteur" => $nomAlice, "date" => '19/04/2020', "estDeMoi" => true ],
                        [ "id" => 1, "desc" => 'des gants de boxe', "auteur" => 'Bob', "date" => '07/04/2020' ],
                    ]
                ];
            case 2:
                return [
                    "nomUtilisateur" => 'Charlie',
                    "idees" => []
                ];
            case 3:
                return [
                    "nomUtilisateur" => 'David',
                    "idees" => []
                ];
        }
    }
}
