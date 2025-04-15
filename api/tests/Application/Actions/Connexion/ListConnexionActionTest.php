<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Connexion;

use App\Application\Actions\ActionPayload;
use App\Domain\Connexion\ConnexionRepository;
use App\Domain\Connexion\Connexion;
use DI\Container;
use Tests\TestCase;

class ListConnexionActionTest extends TestCase
{
    public function testAction()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $connexion = new Connexion(1, 'bill.gates', 'Bill', 'Gates');

        $connexionRepositoryProphecy = $this->prophesize(ConnexionRepository::class);
        $connexionRepositoryProphecy
            ->findAll()
            ->willReturn([$connexion])
            ->shouldBeCalledOnce();

        $container->set(ConnexionRepository::class, $connexionRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/connexion');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedPayload = new ActionPayload(200, [$connexion]);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }
}
