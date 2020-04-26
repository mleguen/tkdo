<?php
declare(strict_types=1);

namespace App\Application\Mock;

class MockData
{
    const alice = [
        "identifiant" => "alice@tkdo.org",
        "nom" => "Alice",
        "mdp" => "Alice",
    ];

    const token = "fake-jwt-token";

    const occasion = [
        "titre" => 'Noël 2020',
        "participants" => [
            [ "id" => 0, "nom" => MockData::alice["nom"], "estMoi" => true],
            [ "id" => 1, "nom" => 'Bob', "aQuiOffrir" => true],
            [ "id" => 2, "nom" => 'Charlie'],
            [ "id" => 3, "nom" => 'David'],
        ],
    ];

    const listesIdees = [
        0 => [
            "nomUtilisateur" => MockData::alice["nom"],
            "estMoi" => true,
            "idees" => [
                [ "id" => 0, "desc" => 'un gauffrier', "auteur" => MockData::alice["nom"], "date" => '19/04/2020', "estDeMoi" => true ],
            ],
        ],
        1 => [
            "nomUtilisateur" => 'Bob',
            "idees" => [
                [ "id" => 0, "desc" => 'une canne à pêche', "auteur" => MockData::alice["nom"], "date" => '19/04/2020', "estDeMoi" => true ],
                [ "id" => 1, "desc" => 'des gants de boxe', "auteur" => 'Bob', "date" => '07/04/2020' ],
            ]
        ],
        2 => [
            "nomUtilisateur" => 'Charlie',
            "idees" => []
        ],
        3 => [
            "nomUtilisateur" => 'David',
            "idees" => []
        ],
    ];
}
