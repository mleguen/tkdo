<?php
declare(strict_types=1);

use App\Domain\Connexion\ConnexionRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Resultat\ResultatRepository;
use App\Infrastructure\Persistence\Connexion\DoctrineConnexionRepository;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateurRepository;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasionRepository;
use App\Infrastructure\Persistence\Idee\DoctrineIdeeRepository;
use App\Infrastructure\Persistence\Resultat\DoctrineResultatRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our XxxRepository interface to its in memory implementation
    $containerBuilder->addDefinitions([
        ConnexionRepository::class => \DI\autowire(DoctrineConnexionRepository::class),
        UtilisateurRepository::class => \DI\autowire(DoctrineUtilisateurRepository::class),
        OccasionRepository::class => \DI\autowire(DoctrineOccasionRepository::class),
        IdeeRepository::class => \DI\autowire(DoctrineIdeeRepository::class),
        ResultatRepository::class => \DI\autowire(DoctrineResultatRepository::class),
    ]);
};
