<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Utilisateur;

use App\Application\Actions\ActionPayload;
use App\Domain\Utilisateur\UtilisateurRepository;
use App\Domain\Utilisateur\Utilisateur;
use DI\Container;
use Tests\TestCase;

class ListUtilisateurActionTest extends TestCase
{
    public function testAction()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $utilisateur = new Utilisateur(1, 'bill.gates', 'Bill', 'Gates');

        $utilisateurRepositoryProphecy = $this->prophesize(UtilisateurRepository::class);
        $utilisateurRepositoryProphecy
            ->findAll()
            ->willReturn([$utilisateur])
            ->shouldBeCalledOnce();

        $container->set(UtilisateurRepository::class, $utilisateurRepositoryProphecy->reveal());

        $request = $this->createRequest('GET', '/users');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedPayload = new ActionPayload(200, [$utilisateur]);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }
}
