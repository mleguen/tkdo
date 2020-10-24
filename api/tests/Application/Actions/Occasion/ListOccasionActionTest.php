<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Occasion;

use App\Application\Actions\ActionPayload;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\Occasion\Occasion;
use DI\Container;
use Tests\TestCase;

class ListOccasionActionTest extends TestCase
{
    public function testAction()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $occasion = new Occasion(1, 'bill.gates', 'Bill', 'Gates');

        $occasionRepositoryProphecy = $this->prophesize(OccasionRepository::class);
        $occasionRepositoryProphecy
            ->findAll()
            ->willReturn([$occasion])
            ->shouldBeCalledOnce();

        $container->set(OccasionRepository::class, $occasionRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/users');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedPayload = new ActionPayload(200, [$occasion]);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }
}
