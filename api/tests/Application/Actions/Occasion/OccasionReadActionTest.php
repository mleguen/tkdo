<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Occasion;

use App\Domain\Occasion\AucuneOccasionException;
use App\Infrastructure\Persistence\Occasion\InMemoryOccasion;
use App\Infrastructure\Persistence\ResultatTirage\InMemoryResultatTirage;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateur;
use Tests\Application\Actions\ActionTestCase;

class OccasionReadActionTest extends ActionTestCase
{
    public function testAction()
    {
        $alice = new InMemoryUtilisateur(0, 'alice@tkdo.org', 'Alice', 'Alice');
        $bob = new InMemoryUtilisateur(1, 'bob@tkdo.org', 'Bob', 'Bob');
        $occasion = new InMemoryOccasion(
            0,
            "Noel 2020",
            [$alice, $bob]
        );
        $this->occasionRepositoryProphecy
            ->readLast()
            ->willReturn($occasion)
            ->shouldBeCalledOnce();

        $resultatTirage = new InMemoryResultatTirage($occasion, $alice, $bob);
        $this->resultatTirageRepositoryProphecy
            ->readByOccasion($occasion)
            ->willReturn([$resultatTirage])
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest('GET', '/occasion');

        $this->assertEqualsResponse(
            200,
            <<<EOT
{
    "id": {$occasion->getId()},
    "titre": "{$occasion->getTitre()}",
    "participants": [
        {
            "id": {$alice->getId()},
            "identifiant": "{$alice->getIdentifiant()}",
            "nom": "{$alice->getNom()}"
        },
        {
            "id": {$bob->getId()},
            "identifiant": "{$bob->getIdentifiant()}",
            "nom": "{$bob->getNom()}"
        }
    ],
    "resultatsTirage": [
        {
            "idQuiOffre": {$resultatTirage->getQuiOffre()->getId()},
            "idQuiRecoit": {$resultatTirage->getQuiRecoit()->getId()}
        }
    ]
}
EOT
            , $response
        );
    }

    public function testActionPasDOccasion()
    {
        $this->occasionRepositoryProphecy
            ->readLast()
            ->willThrow(new AucuneOccasionException())
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest('GET', '/occasion');

        $this->assertEqualsResponse(
            404,
            <<<'EOT'
{
    "type": "RESOURCE_NOT_FOUND",
    "description": "aucune occasion"
}
EOT
            , $response
        );
    }

    public function testActionNonAutorise()
    {
        $response = $this->handleRequest('GET', '/occasion');

        $this->assertEqualsResponse(
            401,
            <<<'EOT'
{
    "type": "UNAUTHENTICATED",
    "description": "Unauthorized."
}
EOT
            , $response
        );
    }
}
