<?php
declare(strict_types=1);

namespace Tests;

use App\Application\Service\AuthService;
use App\Application\Service\MailerService;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\Resultat\ResultatRepository;
use App\Domain\Utilisateur\Genre;
use App\Domain\Utilisateur\UtilisateurRepository;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use DI\Container;
use DI\ContainerBuilder;
use Exception;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;

class TestCase extends PHPUnit_TestCase
{
    /**
     * @var Container
     */
    protected $container;

    /** @var App */
    protected $app;
    /** @var ObjectProphecy */
    protected $ideeRepositoryProphecy;
    /** @var ObjectProphecy */
    protected $mailerServiceProphecy;
    /** @var ObjectProphecy */
    protected $occasionRepositoryProphecy;
    /** @var ObjectProphecy */
    protected $resultatRepositoryProphecy;
    /** @var ObjectProphecy */
    protected $utilisateurRepositoryProphecy;
    /** @var AuthService */
    protected $authService;
    /** @var DoctrineUtilisateur */
    protected $alice;
    /** @var DoctrineUtilisateur */
    protected $bob;
    /** @var DoctrineUtilisateur */
    protected $charlie;
    /** @var string */
    protected $mdpalice;
    /** @var string */
    protected $mdpbob;
    /** @var string */
    protected $mdpcharlie;

    public function setUp(): void
    {
        $this->app = $this->getAppInstance();

        $this->ideeRepositoryProphecy = $this->prophesize(IdeeRepository::class);
        $this->container->set(IdeeRepository::class, $this->ideeRepositoryProphecy->reveal());

        $this->occasionRepositoryProphecy = $this->prophesize(OccasionRepository::class);
        $this->container->set(OccasionRepository::class, $this->occasionRepositoryProphecy->reveal());

        $this->resultatRepositoryProphecy = $this->prophesize(ResultatRepository::class);
        $this->container->set(ResultatRepository::class, $this->resultatRepositoryProphecy->reveal());

        $this->utilisateurRepositoryProphecy = $this->prophesize(UtilisateurRepository::class);
        $this->container->set(UtilisateurRepository::class, $this->utilisateurRepositoryProphecy->reveal());

        $this->mailerServiceProphecy = $this->prophesize(MailerService::class);
        $this->container->set(MailerService::class, $this->mailerServiceProphecy->reveal());

        $this->authService = $this->container->get(AuthService::class);

        $this->mdpalice = 'mdpalice';
        $this->alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice')
            ->setEmail('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp(password_hash($this->mdpalice, PASSWORD_DEFAULT))
            ->setGenre(Genre::Feminin)
            ->setEstAdmin(true);
        $this->mdpbob = 'mdpbob';
        $this->bob = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob')
            ->setEmail('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp(password_hash($this->mdpbob, PASSWORD_DEFAULT))
            ->setGenre(Genre::Masculin)
            ->setEstAdmin(false);
        $this->mdpcharlie = 'mdpcharlie';
        $this->charlie = (new DoctrineUtilisateur(3))
            ->setIdentifiant('charlie')
            ->setEmail('charlie@tkdo.org')
            ->setNom('Charlie')
            ->setMdp(password_hash($this->mdpcharlie, PASSWORD_DEFAULT))
            ->setGenre(Genre::Masculin)
            ->setEstAdmin(false);
    }

    /**
     * @return App
     * @throws Exception
     */
    protected function getAppInstance(): App
    {
        // Instantiate PHP-DI ContainerBuilder
        $containerBuilder = new ContainerBuilder();

        // Container intentionally not compiled for tests.

        // Set up settings
        $settings = require __DIR__ . '/../app/settings.php';
        $settings($containerBuilder);

        // Set up dependencies
        $dependencies = require __DIR__ . '/../app/dependencies.php';
        $dependencies($containerBuilder);

        // Set up repositories
        $repositories = require __DIR__ . '/../app/repositories.php';
        $repositories($containerBuilder);

        // Build PHP-DI Container instance
        $this->container = $containerBuilder->build();

        // Instantiate the app
        AppFactory::setContainer($this->container);
        $app = AppFactory::create();

        // Register middleware
        $middleware = require __DIR__ . '/../app/middleware.php';
        $middleware($app);

        // Register routes
        $routes = require __DIR__ . '/../app/routes.php';
        $routes($app);

        return $app;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $headers
     * @param array  $cookies
     * @param array  $serverParams
     * @return Request
     */
    protected function createRequest(
        string $method,
        string $path,
        $query = '',
        $body = null,
        array $serverParams = [],
        array $headers = [
            'Content-type' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ],
        array $cookies = []
    ): Request {
        $uri = new Uri('', '', 80, $path, $query);
        $stream = (new StreamFactory())->createStream(($body) ?? '');

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new SlimRequest($method, $uri, $h, $cookies, $serverParams, $stream);
    }
}
