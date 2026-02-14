<?php

declare(strict_types=1);

namespace Test\Unit\Appli\ModelAdaptor;

use App\Appli\ModelAdaptor\AppartenanceAdaptor;
use App\Appli\ModelAdaptor\GroupeAdaptor;
use App\Dom\Model\Groupe;
use DateTime;
use PHPUnit\Framework\TestCase;

class GroupeAdaptorTest extends TestCase
{
    public function testConstructorCreatesEntityWithoutId(): void
    {
        $groupe = new GroupeAdaptor();

        $this->assertInstanceOf(Groupe::class, $groupe);
    }

    public function testConstructorCreatesEntityWithOptionalId(): void
    {
        $groupe = new GroupeAdaptor(42);

        $this->assertEquals(42, $groupe->getId());
    }

    public function testDefaultArchiveIsFalse(): void
    {
        $groupe = new GroupeAdaptor();

        $this->assertFalse($groupe->getArchive());
    }

    public function testFluentSettersReturnGroupeInterfaceType(): void
    {
        $groupe = new GroupeAdaptor();
        $dateCreation = new DateTime();

        $result = $groupe
            ->setNom('Famille Dupont')
            ->setArchive(true)
            ->setDateCreation($dateCreation);

        $this->assertInstanceOf(Groupe::class, $result);
        $this->assertEquals('Famille Dupont', $groupe->getNom());
        $this->assertTrue($groupe->getArchive());
        $this->assertSame($dateCreation, $groupe->getDateCreation());
    }

    public function testSetIdReturnsGroupeInterfaceType(): void
    {
        $groupe = new GroupeAdaptor();

        $result = $groupe->setId(99);

        $this->assertInstanceOf(Groupe::class, $result);
        $this->assertEquals(99, $groupe->getId());
    }

    public function testAddAppartenanceAddsToCollection(): void
    {
        $groupe = new GroupeAdaptor(1);
        $groupe->setNom('Test')->setDateCreation(new DateTime());

        $appartenance = new AppartenanceAdaptor($groupe);
        $appartenance->setDateAjout(new DateTime());

        $result = $groupe->addAppartenance($appartenance);

        $this->assertInstanceOf(Groupe::class, $result);
        $this->assertCount(1, $groupe->getAppartenances());
        $this->assertSame($appartenance, $groupe->getAppartenances()[0]);
    }

    public function testGetAppartenancesReturnsArray(): void
    {
        $groupe = new GroupeAdaptor();

        $appartenances = $groupe->getAppartenances();

        $this->assertIsArray($appartenances);
        $this->assertCount(0, $appartenances);
    }

    public function testMultipleAppartenancesCanBeAdded(): void
    {
        $groupe = new GroupeAdaptor(1);
        $groupe->setNom('Test')->setDateCreation(new DateTime());

        $app1 = new AppartenanceAdaptor($groupe);
        $app1->setDateAjout(new DateTime());
        $app2 = new AppartenanceAdaptor($groupe);
        $app2->setDateAjout(new DateTime());

        $groupe->addAppartenance($app1);
        $groupe->addAppartenance($app2);

        $this->assertCount(2, $groupe->getAppartenances());
    }
}
