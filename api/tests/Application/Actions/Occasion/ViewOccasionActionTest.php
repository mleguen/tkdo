<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Occasion;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;
use App\Domain\Occasion\Occasion;
use App\Domain\Occasion\OccasionNotFoundException;
use App\Domain\Occasion\OccasionRepository;
use DI\Container;
use Slim\Middleware\ErrorMiddleware;
use Tests\TestCase;

class ViewOccasionActionTest extends TestCase
{
    public function testAction()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $occasion = new Occasion(1, 'bill.gates', 'Bill', 'Gates');

        $occasionRepositoryProphecy = $this->prophesize(OccasionRepository::class);
        $occasionRepositoryProphecy
            ->findOccasionOfId(1)
            ->willReturn($occasion)
            ->shouldBeCalledOnce();

        $container->set(OccasionRepository::class, $occasionRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/occasion/1');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedPayload = new ActionPayload(200, $occasion);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }

    public function testActionThrowsOccasionNotFoundException()
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

        $occasionRepositoryProphecy = $this->prophesize(OccasionRepository::class);
        $occasionRepositoryProphecy
            ->findOccasionOfId(1)
            ->willThrow(new OccasionNotFoundException())
            ->shouldBeCalledOnce();

        $container->set(OccasionRepository::class, $occasionRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/occasion/1');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedError = new ActionError(ActionError::RESOURCE_NOT_FOUND, 'The occasion you requested does not exist.');
        $expectedPayload = new ActionPayload(404, null, $expectedError);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }
}
