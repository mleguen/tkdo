<?php
declare(strict_types=1);

namespace Tests\Application\Actions;

use App\Application\Handlers\HttpErrorHandler;
use App\Application\Mock\MockData;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\ResultatTirage\ResultatTirageRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
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

    class ActionTestCase extends TestCase
{
    /**
     * @var App
     */
    private $app;

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
    protected $resultatTirageRepositoryProphecy;

    /**
     * @var ObjectProphecy
     */
    protected $utilisateurRepositoryProphecy;

    public function setUp()
    {
        $this->container = require __DIR__ . '/../../../bootstrap.php';
        $this->app = $this->getAppInstance($this->container);

        $this->ideeRepositoryProphecy = $this->prophesize(IdeeRepository::class);
        $this->container->set(IdeeRepository::class, $this->ideeRepositoryProphecy->reveal());

        $this->occasionRepositoryProphecy = $this->prophesize(OccasionRepository::class);
        $this->container->set(OccasionRepository::class, $this->occasionRepositoryProphecy->reveal());

        $this->resultatTirageRepositoryProphecy = $this->prophesize(ResultatTirageRepository::class);
        $this->container->set(ResultatTirageRepository::class, $this->resultatTirageRepositoryProphecy->reveal());

        $this->utilisateurRepositoryProphecy = $this->prophesize(UtilisateurRepository::class);
        $this->container->set(UtilisateurRepository::class, $this->utilisateurRepositoryProphecy->reveal());
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
        $body = null,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        array $serverParams = [],
        array $cookies = []
    ): Request {
        $uri = new Uri('', '', 80, $path);
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
        $body = null
    ): ResponseInterface {
        $request = $this->createRequest($method, $path, $body);
        return $this->app->handle($request);
    }

    protected function handleAuthorizedRequest(
        string $method,
        string $path,
        $body = null
    ): ResponseInterface {
        $request = $this->createRequest($method, $path, $body, [
            'HTTP_ACCEPT' => 'application/json',
        ], [
            'HTTP_AUTHORIZATION' => "Bearer ".MockData::getToken(),
        ]);
        return $this->app->handle($request);
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
