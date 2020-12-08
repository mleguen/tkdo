<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Idee\Idee;
use App\Domain\Utilisateur\UtilisateurNotFoundException;
use App\Infrastructure\Persistence\Idee\DoctrineIdee;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateurReference;
use DateTime;
use DateTimeInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Tests\Application\Actions\ActionTestCase;

class ListIdeeActionTest extends ActionTestCase
{
    /** @var DoctrineIdee */
    private $ideeDeBobPourAlice;
    /** @var DoctrineIdee */
    private $ideeDeAlicePourElleMeme;
    /** @var DoctrineIdee */
    private $ideeDeAliceSupprimee;

    public function setUp(): void
    {
        parent::setup();
        $this->utilisateurRepositoryProphecy
            ->read($this->bob->getId(), true)
            ->willReturn(new InMemoryUtilisateurReference($this->bob->getId()));

        $this->ideeDeBobPourAlice = (new DoctrineIdee(1))
            ->setUtilisateur($this->alice)
            ->setDescription('une idee proposee par Bob')
            ->setAuteur($this->bob)
            ->setDateProposition(DateTime::createFromFormat(DateTimeInterface::ISO8601, '2020-04-19T00:00:00+0000'));
        $this->ideeDeAlicePourElleMeme = (new DoctrineIdee(2))
            ->setUtilisateur($this->alice)
            ->setDescription('une idee proposee par Alice elle-meme')
            ->setAuteur($this->alice)
            ->setDateProposition(DateTime::createFromFormat(DateTimeInterface::ISO8601, '2020-10-22T00:00:00+0000'));
        $this->ideeDeAliceSupprimee = (new DoctrineIdee(3))
            ->setUtilisateur($this->alice)
            ->setDescription('une idee proposee puis supprimee par Alice')
            ->setAuteur($this->alice)
            ->setDateProposition(DateTime::createFromFormat(DateTimeInterface::ISO8601, '2020-10-21T00:00:00+0000'))
            ->setDateSuppression(DateTime::createFromFormat(DateTimeInterface::ISO8601, '2020-10-24T00:00:00+0000'));
    }

    /**
     * @dataProvider providerAction
     */
    public function testAction(string $case, bool $supprimee = null)
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();
        $this->ideeRepositoryProphecy
            ->readAllByUtilisateur($this->alice, $supprimee)
            ->willReturn(array_merge(
                $supprimee === true ? [] : [
                    $this->ideeDeBobPourAlice,
                    $this->ideeDeAlicePourElleMeme,
                ],
                $supprimee === false ? [] : [
                    $this->ideeDeAliceSupprimee,
                ]
            ))
            ->shouldBeCalledOnce();

        $qsSupprimees = is_null($supprimee) ? '' : '&supprimee=' . ($supprimee ? '1' : '0');
        $response = $this->handleAuthRequest(
            ($case === 'charlie' ? $this->charlie : ($case === 'bob' ? $this->bob : $this->alice))->getId(),
            is_null($supprimee) || $supprimee,
            'GET',
            '/idee',
            "idUtilisateur={$this->alice->getId()}$qsSupprimees"
        );

        $morceauxReponseIdee = [];
        if ($supprimee !== true) {
            if ($case !== 'alice') {
                $morceauxReponseIdee[] = $this->morceauReponseIdee($this->ideeDeBobPourAlice);
            }
            $morceauxReponseIdee[] = $this->morceauReponseIdee($this->ideeDeAlicePourElleMeme);
        }
        if ($supprimee !== false) {
            $morceauxReponseIdee[] = $this->morceauReponseIdee($this->ideeDeAliceSupprimee);
        }
        $morceauxReponseIdee = implode(",\n", $morceauxReponseIdee);

        $json = <<<EOT
{
    "utilisateur": {
        "genre": "{$this->alice->getGenre()}",
        "id": {$this->alice->getId()},
        "nom": "{$this->alice->getNom()}"
    },
    "idees": [
{$morceauxReponseIdee}
    ]
}

EOT;
        $this->assertEquals($json, (string)$response->getBody());
    }

    public function providerAction()
    {
        return [
            ['charlie', false], // tiers
            ['bob', false], // auteur
            ['alice', false], // utilisateur
            ['charlie', true], // admin, idées supprimées seulement
            ['charlie'], // admin, toutes les idées
        ];
    }

    private function morceauReponseIdee(Idee $i)
    {
        $dateSuppression = !$i->hasDateSuppression() ? '' : <<<EOT
,
            "dateSuppression": "{$i->getDateSuppression()->format(DateTimeInterface::ISO8601)}"
EOT;
        return <<<EOT
        {
            "id": {$i->getId()},
            "description": "{$i->getDescription()}",
            "auteur": {
                "genre": "{$i->getAuteur()->getGenre()}",
                "id": {$i->getAuteur()->getId()},
                "nom": "{$i->getAuteur()->getNom()}"
            },
            "dateProposition": "{$i->getDateProposition()->format(DateTimeInterface::ISO8601)}"$dateSuppression
        }
EOT;
    }

    public function testActionIdUtilisateurManquant()
    {
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('idUtilisateur manquant');
        $this->handleAuthRequest(
            $this->charlie->getId(),
            false,
            'GET',
            '/idee',
            ''
        );
    }

    public function testActionToutesPasAdmin()
    {
        $this->expectException(HttpForbiddenException::class);
        $this->handleAuthRequest(
            $this->charlie->getId(),
            false,
            'GET',
            '/idee',
            "idUtilisateur={$this->alice->getId()}"
        );
    }

    public function testActionSupprimeesPasAdmin()
    {
        $this->expectException(HttpForbiddenException::class);
        $this->handleAuthRequest(
            $this->charlie->getId(),
            false,
            'GET',
            '/idee',
            "idUtilisateur={$this->alice->getId()}&supprimee=1"
        );
    }

    public function testActionUtilisateurInconnu()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willThrow(new UtilisateurNotFoundException())
            ->shouldBeCalledOnce();

        $this->expectException(HttpNotFoundException::class);
        $this->expectExceptionMessage('utilisateur inconnu');
        $this->handleAuthRequest(
            $this->charlie->getId(),
            false,
            'GET',
            '/idee',
            "idUtilisateur={$this->alice->getId()}&supprimee=0"
        );
    }

    public function testActionNonAutorise()
    {
        $this->expectException(HttpUnauthorizedException::class);
        $this->handleRequest('GET', '/idee', "idUtilisateur={$this->alice->getId()}&supprimee=0");
    }
}
