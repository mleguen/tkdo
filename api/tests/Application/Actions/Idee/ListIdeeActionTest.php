<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Application\Actions\ActionPayload;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Idee\Idee;
use DI\Container;
use Tests\TestCase;

class ListIdeeActionTest extends TestCase
{
    public function testAction()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $idee = new Idee(1, 'bill.gates', 'Bill', 'Gates');

        $ideeRepositoryProphecy = $this->prophesize(IdeeRepository::class);
        $ideeRepositoryProphecy
            ->findAll()
            ->willReturn([$idee])
            ->shouldBeCalledOnce();

        $container->set(IdeeRepository::class, $ideeRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/idee');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedPayload = new ActionPayload(200, [$idee]);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }
}
