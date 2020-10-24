<?php
declare(strict_types=1);

namespace Tests\Application\Actions;

use App\Application\Handlers\HttpErrorHandler;
use App\Application\Service\AuthService;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\Resultat\ResultatRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use DI\Container;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;
use Tests\TestCase;

abstract class ActionTestCase extends TestCase
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var ObjectProphecy
     */
    protected $ideeRepositoryProphecy;

    /**
     * @var ObjectProphecy
     */
    protected $occasionRepositoryProphecy;

    /**
     * @var ObjectProphecy
     */
    protected $resultatRepositoryProphecy;

    /**
     * @var ObjectProphecy
     */
    protected $utilisateurRepositoryProphecy;

    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * @var DoctrineUtilisateur
     */
    protected $alice;

    /**
     * @var DoctrineUtilisateur
     */
    protected $bob;

    /**
     * @var DoctrineUtilisateur
     */
    protected $charlie;

    /**
     * @var string
     */
    protected $clePrivee;

    /**
     * @var string
     */
    protected $clePublique;

    public function setUp()
    {
        $this->container = require __DIR__ . '/../../../bootstrap.php';
        $this->app = $this->getAppInstance($this->container);

        $this->ideeRepositoryProphecy = $this->prophesize(IdeeRepository::class);
        $this->container->set(IdeeRepository::class, $this->ideeRepositoryProphecy->reveal());

        $this->occasionRepositoryProphecy = $this->prophesize(OccasionRepository::class);
        $this->container->set(OccasionRepository::class, $this->occasionRepositoryProphecy->reveal());

        $this->resultatRepositoryProphecy = $this->prophesize(ResultatRepository::class);
        $this->container->set(ResultatRepository::class, $this->resultatRepositoryProphecy->reveal());

        $this->utilisateurRepositoryProphecy = $this->prophesize(UtilisateurRepository::class);
        $this->container->set(UtilisateurRepository::class, $this->utilisateurRepositoryProphecy->reveal());
        
        $this->authService = new AuthService([
            'algo' => 'RS256',
            'fichierClePrivee' => __DIR__ . '/../../../var/auth/auth_rsa',
            'fichierClePublique' => __DIR__ . '/../../../var/auth/auth_rsa.pub',
            'validite' => 3600,
        ]);
        $this->container->set(AuthService::class, $this->authService);

        $this->alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice');
        $this->bob = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp('mdpbob');
        $this->charlie = (new DoctrineUtilisateur(3))
            ->setIdentifiant('charlie@tkdo.org')
            ->setNom('Charlie')
            ->setMdp('mdpcharlie');
    }

    /**
     * @return App
     * @throws Exception
     */
    private function getAppInstance(Container $container): App
    {
        // Instantiate the app
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        // Register middleware
        $middleware = require __DIR__ . '/../../../app/middleware.php';
        $middleware($app);

        // Register routes
        $routes = require __DIR__ . '/../../../app/routes.php';
        $routes($app);

        $callableResolver = $app->getCallableResolver();
        $responseFactory = $app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $app->add($errorMiddleware);

        return $app;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $headers
     * @param array  $serverParams
     * @param array  $cookies
     * @return Request
     */
    protected function createRequest(
        string $method,
        string $path,
        $query = '',
        $body = null,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        array $serverParams = [],
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

    protected function handleRequest(
        string $method,
        string $path,
        $query = '',
        $body = null
    ): ResponseInterface {
        $request = $this->createRequest($method, $path, $query, $body);
        return $this->app->handle($request);
    }

    protected function handleRequestWithAuthHeader(
        string $authHeader,
        string $method,
        string $path,
        $query = '',
        $body = null
    ): ResponseInterface {
        $request = $this->createRequest($method, $path, $query, $body, [
            'HTTP_ACCEPT' => 'application/json',
        ], [
            'HTTP_AUTHORIZATION' => $authHeader,
        ]);
        return $this->app->handle($request);
    }

    protected function handleAuthRequest(
        int $idUtilisateurAuth,
        string $method,
        string $path,
        $query = '',
        $body = null
    ): ResponseInterface {
        return $this->handleRequestWithAuthHeader(
            "Bearer " . $this->authService->encodeBearerToken($idUtilisateurAuth),
            $method, $path, $query, $body
        );
    }

    protected function assertEqualsResponse(
        int $statusCode,
        ?string $jsonData,
        ResponseInterface $response
    ) {
        $this->assertEquals($jsonData, (string) $response->getBody());
        $this->assertEquals($statusCode, $response->getStatusCode());
    } 
}
