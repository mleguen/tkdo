<?php
declare(strict_types=1);

use App\Domain\Idee\IdeeRepository;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\ResultatTirage\ResultatTirageRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
use App\Infrastructure\Persistence\Idee\InMemoryIdee;
use App\Infrastructure\Persistence\Idee\InMemoryIdeeRepository;
use App\Infrastructure\Persistence\Occasion\InMemoryOccasion;
use App\Infrastructure\Persistence\Occasion\InMemoryOccasionRepository;
use App\Infrastructure\Persistence\ResultatTirage\InMemoryResultatTirage;
use App\Infrastructure\Persistence\ResultatTirage\InMemoryResultatTirageRepository;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateur;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateurRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $alice = new InMemoryUtilisateur(0, 'alice@tkdo.org', 'Alice', 'Alice');
    $bob = new InMemoryUtilisateur(1, 'bob@tkdo.org', 'Bob', 'Bob');
    $charlie = new InMemoryUtilisateur(2, 'charlie@tkdo.org', 'Charlie', 'Charlie');
    $david = new InMemoryUtilisateur(3, 'david@tkdo.org', 'David', 'David');

    $occasion = new InMemoryOccasion(
        0,
        "Noël 2020",
        [$alice, $bob,$charlie, $david]
    );

    // Here we map repository interfaces to in memory implementations
    $containerBuilder->addDefinitions([
        UtilisateurRepository::class => \DI\value(new InMemoryUtilisateurRepository([
            $alice,
            $bob,
            $charlie,
            $david,
        ])),
        IdeeRepository::class => \DI\value(new InMemoryIdeeRepository([
            new InMemoryIdee(
                0,
                $alice,
                "un gauffrier",
                $alice,
                \DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-04-19T00:00:00+0000')
            ),
            new InMemoryIdee(
                1,
                $bob,
                "une canne à pêche",
                $alice,
                \DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-04-19T00:00:00+0000')
            ),
            new InMemoryIdee(
                2,
                $bob,
                "des gants de boxe",
                $bob,
                \DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-04-07T00:00:00+0000')
            ),
        ])),
        OccasionRepository::class => \DI\value(new InMemoryOccasionRepository([
            $occasion
        ])),
        ResultatTirageRepository::class => \DI\value(new InMemoryResultatTirageRepository([
            new InMemoryResultatTirage($occasion, $alice, $bob),
        ])),
    ]);
};
