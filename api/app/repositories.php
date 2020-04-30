<?php
declare(strict_types=1);

use App\Domain\Idee\IdeeRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
use App\Infrastructure\Persistence\Idee\SessionIdeeRepository;
use App\Infrastructure\Persistence\Utilisateur\SessionUtilisateurRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its session implementation
    session_start();
    $containerBuilder->addDefinitions([
        UtilisateurRepository::class => \DI\autowire(SessionUtilisateurRepository::class),
        IdeeRepository::class => \DI\autowire(SessionIdeeRepository::class),
    ]);
};
