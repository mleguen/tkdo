<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Connexion;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;
use App\Domain\Connexion\Connexion;
use App\Domain\Connexion\ConnexionNotFoundException;
use App\Domain\Connexion\ConnexionRepository;
use DI\Container;
use Slim\Middleware\ErrorMiddleware;
use Tests\TestCase;

class ViewConnexionActionTest extends TestCase
{
    public function testAction()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $connexion = new Connexion(1, 'bill.gates', 'Bill', 'Gates');

        $connexionRepositoryProphecy = $this->prophesize(ConnexionRepository::class);
        $connexionRepositoryProphecy
            ->findConnexionOfId(1)
            ->willReturn($connexion)
            ->shouldBeCalledOnce();

        $container->set(ConnexionRepository::class, $connexionRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/users/1');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedPayload = new ActionPayload(200, $connexion);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }

    public function testActionThrowsConnexionNotFoundException()
    {
        $app = $this->getAppInstance();

        $callableResolver = $app->getCallableResolver();
        $responseFactory = $app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false ,false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $app->add($errorMiddleware);

        /** @var Container $container */
        $container = $app->getContainer();

        $connexionRepositoryProphecy = $this->prophesize(ConnexionRepository::class);
        $connexionRepositoryProphecy
            ->findConnexionOfId(1)
            ->willThrow(new ConnexionNotFoundException())
            ->shouldBeCalledOnce();

        $container->set(ConnexionRepository::class, $connexionRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/users/1');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedError = new ActionError(ActionError::RESOURCE_NOT_FOUND, 'The connexion you requested does not exist.');
        $expectedPayload = new ActionPayload(404, null, $expectedError);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }
}
