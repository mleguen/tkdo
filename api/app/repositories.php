<?php
declare(strict_types=1);

use App\Domain\Idee\IdeeRepository;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\ResultatTirage\ResultatTirageRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
use App\Infrastructure\Persistence\Idee\DoctrineIdeeRepository;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasionRepository;
use App\Infrastructure\Persistence\ResultatTirage\DoctrineResultatTirageRepository;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateurRepository;
use DI\ContainerBuilder;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        EntityManager::class => function (ContainerInterface $container) {
            $doctrineSettings = $container->get('settings')['doctrine'];
            $config = Setup::createConfiguration(
                $doctrineSettings['dev_mode']
            );

            $config->setMetadataDriverImpl(
                new AnnotationDriver(
                    new AnnotationReader(),
                    $doctrineSettings['metadata_dirs']
                )
            );

            $config->setMetadataCacheImpl(
                new PhpFileCache(
                    $doctrineSettings['metadata_cache_dir']
                )
            );

            $config->setProxyDir($doctrineSettings['proxy_cache_dir']);

            $config->setQueryCacheImpl(
                new PhpFileCache(
                    $doctrineSettings['query_cache_dir']
                )
            );

            return EntityManager::create(
                $doctrineSettings['connection'],
                $config
            );
        },
        UtilisateurRepository::class => \DI\autowire(DoctrineUtilisateurRepository::class),
        OccasionRepository::class => \DI\autowire(DoctrineOccasionRepository::class),
        IdeeRepository::class => \DI\autowire(DoctrineIdeeRepository::class),
        ResultatTirageRepository::class => \DI\autowire(DoctrineResultatTirageRepository::class),
    ]);
};
