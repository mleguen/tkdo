<?php
declare(strict_types=1);

namespace Test\Unit\Appli\Service;

use App\Appli\Service\AuthService;
use App\Appli\Service\DateService;
use App\Appli\Service\JsonService;
use App\Dom\Model\Groupe;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class JsonServiceGroupeTest extends TestCase
{
    use ProphecyTrait;

    private JsonService $jsonService;

    #[\Override]
    protected function setUp(): void
    {
        $authServiceProphecy = $this->prophesize(AuthService::class);
        $dateServiceProphecy = $this->prophesize(DateService::class);
        $this->jsonService = new JsonService(
            $authServiceProphecy->reveal(),
            $dateServiceProphecy->reveal()
        );
    }

    public function testEncodeListeGroupesWithActiveAndArchived(): void
    {
        $actif1 = $this->prophesize(Groupe::class);
        $actif1->getId()->willReturn(1);
        $actif1->getNom()->willReturn('Famille');
        $actif1->getArchive()->willReturn(false);

        $actif2 = $this->prophesize(Groupe::class);
        $actif2->getId()->willReturn(3);
        $actif2->getNom()->willReturn('Amis');
        $actif2->getArchive()->willReturn(false);

        $archive = $this->prophesize(Groupe::class);
        $archive->getId()->willReturn(2);
        $archive->getNom()->willReturn('Noël 2024');
        $archive->getArchive()->willReturn(true);

        $json = $this->jsonService->encodeListeGroupes(
            [$actif1->reveal(), $actif2->reveal()],
            [$archive->reveal()],
            [3]
        );

        $data = json_decode($json, true);

        $this->assertCount(2, $data['actifs']);
        $this->assertCount(1, $data['archives']);

        $this->assertEquals(1, $data['actifs'][0]['id']);
        $this->assertEquals('Famille', $data['actifs'][0]['nom']);
        $this->assertFalse($data['actifs'][0]['archive']);
        $this->assertFalse($data['actifs'][0]['estAdmin']);

        $this->assertEquals(3, $data['actifs'][1]['id']);
        $this->assertEquals('Amis', $data['actifs'][1]['nom']);
        $this->assertFalse($data['actifs'][1]['archive']);
        $this->assertTrue($data['actifs'][1]['estAdmin']);

        $this->assertEquals(2, $data['archives'][0]['id']);
        $this->assertEquals('Noël 2024', $data['archives'][0]['nom']);
        $this->assertTrue($data['archives'][0]['archive']);
        $this->assertFalse($data['archives'][0]['estAdmin']);
    }

    public function testEncodeListeGroupesWithEmptyArrays(): void
    {
        $json = $this->jsonService->encodeListeGroupes([], [], []);

        $data = json_decode($json, true);

        $this->assertCount(0, $data['actifs']);
        $this->assertCount(0, $data['archives']);
    }

    public function testEncodeListeGroupesEstAdminFromAdminIds(): void
    {
        $groupe = $this->prophesize(Groupe::class);
        $groupe->getId()->willReturn(5);
        $groupe->getNom()->willReturn('Test');
        $groupe->getArchive()->willReturn(false);

        $json = $this->jsonService->encodeListeGroupes(
            [$groupe->reveal()],
            [],
            [5]
        );

        $data = json_decode($json, true);
        $this->assertTrue($data['actifs'][0]['estAdmin']);
    }

    public function testEncodeListeGroupesKeysAreSorted(): void
    {
        $groupe = $this->prophesize(Groupe::class);
        $groupe->getId()->willReturn(1);
        $groupe->getNom()->willReturn('Test');
        $groupe->getArchive()->willReturn(false);

        $json = $this->jsonService->encodeListeGroupes(
            [$groupe->reveal()],
            [],
            []
        );

        $data = json_decode($json, true);
        $keys = array_keys($data['actifs'][0]);
        $sortedKeys = $keys;
        sort($sortedKeys);
        $this->assertEquals($sortedKeys, $keys);
    }
}
