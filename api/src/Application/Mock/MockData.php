<?php
declare(strict_types=1);

namespace App\Application\Mock;

class MockData
{
    const alice =
    [
        "identifiant" => "alice@tkdo.org",
        "nom" => "Alice",
        "mdp" => "Alice",
    ];

    const token = "fake-jwt-token";

    const occasion =
    [
        "titre" => 'Noël 2020',
        "participants" =>
        [
            [ "id" => 0, "nom" => MockData::alice["nom"], "estMoi" => true],
            [ "id" => 1, "nom" => 'Bob', "aQuiOffrir" => true],
            [ "id" => 2, "nom" => 'Charlie'],
            [ "id" => 3, "nom" => 'David'],
        ]
    ];
}
