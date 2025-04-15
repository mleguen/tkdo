<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Resultat;

use App\Application\Actions\ActionPayload;
use App\Domain\Resultat\ResultatRepository;
use App\Domain\Resultat\Resultat;
use DI\Container;
use Tests\TestCase;

class ListResultatActionTest extends TestCase
{
    public function testAction()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $resultat = new Resultat(1, 'bill.gates', 'Bill', 'Gates');

        $resultatRepositoryProphecy = $this->prophesize(ResultatRepository::class);
        $resultatRepositoryProphecy
            ->findAll()
            ->willReturn([$resultat])
            ->shouldBeCalledOnce();

        $container->set(ResultatRepository::class, $resultatRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/resultat');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedPayload = new ActionPayload(200, [$resultat]);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }
}
