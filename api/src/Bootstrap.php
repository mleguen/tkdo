<?php
declare(strict_types=1);

namespace App;

use App\Appli\Controller\CreateConnexionController;
use App\Appli\Controller\CreateExclusionUtilisateurController;
use App\Appli\Controller\CreateIdeeController;
use App\Appli\Controller\CreateIdeeSuppressionController;
use App\Appli\Controller\CreateOccasionController;
use App\Appli\Controller\CreateParticipantOccasionController;
use App\Appli\Controller\CreateResultatOccasionController;
use App\Appli\Controller\CreateTirageOccasionController;
use App\Appli\Controller\CreateUtilisateurController;
use App\Appli\Controller\CreateUtilisateurReinitMdpController;
use App\Appli\Controller\EditOccasionController;
use App\Appli\Controller\EditUtilisateurController;
use App\Appli\Controller\ListExclusionUtilisateurController;
use App\Appli\Controller\ListIdeeController;
use App\Appli\Controller\ListOccasionController;
use App\Appli\Controller\ListUtilisateurController;
use App\Appli\Controller\ViewOccasionController;
use App\Appli\Controller\ViewUtilisateurController;
use App\Appli\Handler\AppJsonErrorRenderer;
use App\Appli\Handler\AppLogger;
use App\Appli\Handler\AppPlainTextErrorRenderer;
use App\Appli\Middleware\AuthMiddleware;
use App\Appli\PluginAdaptor\MailPluginAdaptor;
use App\Appli\PluginAdaptor\PasswordPluginAdaptor;
use App\Appli\RepositoryAdaptor\ExclusionRepositoryAdaptor;
use App\Appli\RepositoryAdaptor\IdeeRepositoryAdaptor;
use App\Appli\RepositoryAdaptor\OccasionRepositoryAdaptor;
use App\Appli\RepositoryAdaptor\ResultatRepositoryAdaptor;
use App\Appli\RepositoryAdaptor\UtilisateurRepositoryAdaptor;
use App\Appli\Settings\DoctrineSettings;
use App\Dom\Plugin\MailPlugin;
use App\Dom\Plugin\PasswordPlugin;
use App\Dom\Repository\IdeeRepository;
use App\Dom\Repository\OccasionRepository;
use App\Dom\Repository\ResultatRepository;
use App\Dom\Repository\UtilisateurRepository;
use App\Appli\Settings\ErrorSettings;
use App\Dom\Repository\ExclusionRepository;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;
use Dotenv\Dotenv;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Handlers\ErrorHandler;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Bootstrap
{
    /** @var Container */
    private $container;
    /** @var App */
    private $slimApp;

    public $apiRoot = __DIR__ . '/..';
    /** @var bool */
    public $devMode = true;
    public $docker = false;

    public function initEnv()
    {
        // Load .env
        $dotenv = Dotenv::createUnsafeImmutable($this->apiRoot);
        $dotenv->load();

        $this->devMode = boolval(getenv('TKDO_DEV_MODE') ?: '1');
        $this->docker = getenv('docker') !== false;
    }

    public function initContainer(): Container
    {
        // Instantiate PHP-DI ContainerBuilder
        $containerBuilder = new ContainerBuilder();
        if (!$this->devMode) $containerBuilder->enableCompilation($this->apiRoot . '/var/cache');

        // Define all non implicit dependencies
        $containerBuilder->addDefinitions([

            Bootstrap::class => $this,

            EntityManager::class => function (DoctrineSettings $settings) {
                return EntityManager::create(
                    $settings->connection,
                    $settings->config
                );
            },

            ExclusionRepository::class => \DI\autowire(ExclusionRepositoryAdaptor::class),
            IdeeRepository::class => \DI\autowire(IdeeRepositoryAdaptor::class),
            LoggerInterface::class => \DI\autowire(AppLogger::class),
            MailPlugin::class => \DI\autowire(MailPluginAdaptor::class),
            OccasionRepository::class => \DI\autowire(OccasionRepositoryAdaptor::class),
            PasswordPlugin::class => \DI\autowire(PasswordPluginAdaptor::class),
            ResultatRepository::class => \DI\autowire(ResultatRepositoryAdaptor::class),
            UtilisateurRepository::class => \DI\autowire(UtilisateurRepositoryAdaptor::class),
        ]);

        // Build PHP-DI Container instance
        $this->container = $containerBuilder->build();
        return $this->container;
    }

    public function initDoctrineMigration(): DependencyFactory
    {
        $config = $this->container->get(ConfigurationArray::class);
        $entityManager = $this->container->get(EntityManager::class);

        return DependencyFactory::fromEntityManager($config, new ExistingEntityManager($entityManager));
    }

    public function initSlimApp(): App
    {
        // Instantiate the app
        AppFactory::setContainer($this->container);
        $this->slimApp = AppFactory::create();
        $basePath = getenv('TKDO_API_BASE_PATH');
        if ($basePath) $this->slimApp->setBasePath($basePath);

        $this->slimApp->add(AuthMiddleware::class);

        $this->slimApp->options('/{routes:.*}', function (Request $request, Response $response) {
            // CORS Pre-Flight OPTIONS Request Handler
            return $response;
        });

        $this->slimApp->post('/connexion', CreateConnexionController::class);
        $this->slimApp->group('/idee', function (RouteCollectorProxyInterface $group) {
            $group->get('', ListIdeeController::class);
            $group->post('', CreateIdeeController::class);
            $group->post('/{idIdee}/suppression', CreateIdeeSuppressionController::class);
        });
        $this->slimApp->group('/occasion', function (RouteCollectorProxyInterface $group) {
            $group->get('', ListOccasionController::class);
            $group->post('', CreateOccasionController::class);
            $group->group('/{idOccasion}', function (RouteCollectorProxyInterface $group) {
                $group->get('', ViewOccasionController::class);
                $group->put('', EditOccasionController::class);
                $group->post('/participant', CreateParticipantOccasionController::class);
                $group->post('/resultat', CreateResultatOccasionController::class);
                $group->post('/tirage', CreateTirageOccasionController::class);
            });
        });
        $this->slimApp->group('/utilisateur', function (RouteCollectorProxyInterface $group) {
            $group->get('', ListUtilisateurController::class);
            $group->post('', CreateUtilisateurController::class);
            $group->group('/{idUtilisateur}', function (RouteCollectorProxyInterface $group) {
                $group->get('', ViewUtilisateurController::class);
                $group->put('', EditUtilisateurController::class);
                $group->post('/reinitmdp', CreateUtilisateurReinitMdpController::class);
                $group->group('/exclusion', function (RouteCollectorProxyInterface $group) {
                    $group->get('', ListExclusionUtilisateurController::class);
                    $group->post('', CreateExclusionUtilisateurController::class);
                });
            });
        });

        // Add Routing Middleware
        $this->slimApp->addRoutingMiddleware();

        return $this->slimApp;
    }

    public function initSlimErrorHandling(): ErrorHandler
    {
        $errorHandler = new ErrorHandler(
            $this->slimApp->getCallableResolver(),
            $this->slimApp->getResponseFactory()
        );
        $errorHandler->setLogErrorRenderer($this->container->get(AppPlainTextErrorRenderer::class));
        $errorHandler->registerErrorRenderer(
            'application/json',
            $this->container->get(AppJsonErrorRenderer::class)
        );
        $errorHandler->setDefaultErrorRenderer(
            'application/json',
            $this->container->get(AppJsonErrorRenderer::class)
        );

        /** @var ErrorSettings */
        $errorSettings = $this->container->get(ErrorSettings::class);
        $errorMiddleware = $this->slimApp->addErrorMiddleware(
            $errorSettings->displayErrorDetails,
            $errorSettings->logErrors,
            $errorSettings->logErrorDetails
        );
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        return $errorHandler;
    }
}
