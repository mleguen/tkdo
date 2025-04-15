<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;
use App\Domain\Idee\Idee;
use App\Domain\Idee\IdeeNotFoundException;
use App\Domain\Idee\IdeeRepository;
use DI\Container;
use Slim\Middleware\ErrorMiddleware;
use Tests\TestCase;

class ViewIdeeActionTest extends TestCase
{
    public function testAction()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $idee = new Idee(1, 'bill.gates', 'Bill', 'Gates');

        $ideeRepositoryProphecy = $this->prophesize(IdeeRepository::class);
        $ideeRepositoryProphecy
            ->findIdeeOfId(1)
            ->willReturn($idee)
            ->shouldBeCalledOnce();

        $container->set(IdeeRepository::class, $ideeRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/idee/1');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedPayload = new ActionPayload(200, $idee);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }

    public function testActionThrowsIdeeNotFoundException()
    {
        $app = $this->getAppInstance();

        $callableResolver = $app->getCallableResolver();
        $responseFactory = $app->getResponseFactory();

        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        $app->add($errorMiddleware);

        /** @var Container $container */
        $container = $app->getContainer();

        $ideeRepositoryProphecy = $this->prophesize(IdeeRepository::class);
        $ideeRepositoryProphecy
            ->findIdeeOfId(1)
            ->willThrow(new IdeeNotFoundException())
            ->shouldBeCalledOnce();

        $container->set(IdeeRepository::class, $ideeRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/idee/1');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedError = new ActionError(ActionError::RESOURCE_NOT_FOUND, 'The idee you requested does not exist.');
        $expectedPayload = new ActionPayload(404, null, $expectedError);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }
}
