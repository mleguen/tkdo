<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Resultat;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;
use App\Domain\Resultat\Resultat;
use App\Domain\Resultat\ResultatNotFoundException;
use App\Domain\Resultat\ResultatRepository;
use DI\Container;
use Slim\Middleware\ErrorMiddleware;
use Tests\TestCase;

class ViewResultatActionTest extends TestCase
{
    public function testAction()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $resultat = new Resultat(1, 'bill.gates', 'Bill', 'Gates');

        $resultatRepositoryProphecy = $this->prophesize(ResultatRepository::class);
        $resultatRepositoryProphecy
            ->findResultatOfId(1)
            ->willReturn($resultat)
            ->shouldBeCalledOnce();

        $container->set(ResultatRepository::class, $resultatRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/resultat/1');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedPayload = new ActionPayload(200, $resultat);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }

    public function testActionThrowsResultatNotFoundException()
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

        $resultatRepositoryProphecy = $this->prophesize(ResultatRepository::class);
        $resultatRepositoryProphecy
            ->findResultatOfId(1)
            ->willThrow(new ResultatNotFoundException())
            ->shouldBeCalledOnce();

        $container->set(ResultatRepository::class, $resultatRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/resultat/1');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedError = new ActionError(ActionError::RESOURCE_NOT_FOUND, 'The resultat you requested does not exist.');
        $expectedPayload = new ActionPayload(404, null, $expectedError);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }
}
