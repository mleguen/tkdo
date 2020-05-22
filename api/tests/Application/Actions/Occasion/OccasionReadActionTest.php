<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Occasion;

use App\Domain\Occasion\AucuneOccasionException;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use App\Infrastructure\Persistence\ResultatTirage\DoctrineResultatTirage;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Tests\Application\Actions\ActionTestCase;

class OccasionReadActionTest extends ActionTestCase
{
    public function testAction()
    {
        $alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice');
        $bob = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp('mdpbob');
        $occasion = (new DoctrineOccasion(1))
            ->setTitre('Noel 2020')
            ->setParticipants([$alice, $bob]);
        $this->occasionRepositoryProphecy
            ->readLast()
            ->willReturn($occasion)
            ->shouldBeCalledOnce();

        $resultatTirage = (new DoctrineResultatTirage($occasion, $alice))
            ->setQuiRecoit($bob);
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
