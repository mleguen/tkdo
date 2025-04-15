<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Utilisateur;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Application\Handlers\HttpErrorHandler;
use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurNotFoundException;
use App\Domain\Utilisateur\UtilisateurRepository;
use DI\Container;
use Slim\Middleware\ErrorMiddleware;
use Tests\TestCase;

class ViewUtilisateurActionTest extends TestCase
{
    public function testAction()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $utilisateur = new Utilisateur(1, 'bill.gates', 'Bill', 'Gates');

        $utilisateurRepositoryProphecy = $this->prophesize(UtilisateurRepository::class);
        $utilisateurRepositoryProphecy
            ->findUtilisateurOfId(1)
            ->willReturn($utilisateur)
            ->shouldBeCalledOnce();

        $container->set(UtilisateurRepository::class, $utilisateurRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/utilisateur/1');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedPayload = new ActionPayload(200, $utilisateur);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }

    public function testActionThrowsUtilisateurNotFoundException()
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

        $utilisateurRepositoryProphecy = $this->prophesize(UtilisateurRepository::class);
        $utilisateurRepositoryProphecy
            ->findUtilisateurOfId(1)
            ->willThrow(new UtilisateurNotFoundException())
            ->shouldBeCalledOnce();

        $container->set(UtilisateurRepository::class, $utilisateurRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/utilisateur/1');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedError = new ActionError(ActionError::RESOURCE_NOT_FOUND, 'The utilisateur you requested does not exist.');
        $expectedPayload = new ActionPayload(404, null, $expectedError);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }
}
