<?php

declare(strict_types=1);

namespace Test\Unit\Appli\ModelAdaptor;

use App\Appli\ModelAdaptor\AppartenanceAdaptor;
use App\Appli\ModelAdaptor\GroupeAdaptor;
use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use App\Dom\Model\Appartenance;
use DateTime;
use PHPUnit\Framework\TestCase;

class AppartenanceAdaptorTest extends TestCase
{
    public function testConstructorWithoutParams(): void
    {
        $appartenance = new AppartenanceAdaptor();

        $this->assertInstanceOf(Appartenance::class, $appartenance);
    }

    public function testConstructorWithGroupeOnly(): void
    {
        $groupe = new GroupeAdaptor(1);

        $appartenance = new AppartenanceAdaptor($groupe);

        $this->assertSame($groupe, $appartenance->getGroupe());
    }

    public function testConstructorWithBothParams(): void
    {
        $groupe = new GroupeAdaptor(1);
        $utilisateur = new UtilisateurAdaptor(2);

        $appartenance = new AppartenanceAdaptor($groupe, $utilisateur);

        $this->assertSame($groupe, $appartenance->getGroupe());
        $this->assertSame($utilisateur, $appartenance->getUtilisateur());
    }

    public function testDefaultEstAdminIsFalse(): void
    {
        $appartenance = new AppartenanceAdaptor();

        $this->assertFalse($appartenance->getEstAdmin());
    }

    public function testFluentSettersReturnAppartenanceInterfaceType(): void
    {
        $groupe = new GroupeAdaptor(1);
        $utilisateur = new UtilisateurAdaptor(2);
        $dateAjout = new DateTime();

        $appartenance = new AppartenanceAdaptor();

        $result = $appartenance
            ->setGroupe($groupe)
            ->setUtilisateur($utilisateur)
            ->setEstAdmin(true)
            ->setDateAjout($dateAjout);

        $this->assertInstanceOf(Appartenance::class, $result);
        $this->assertSame($groupe, $appartenance->getGroupe());
        $this->assertSame($utilisateur, $appartenance->getUtilisateur());
        $this->assertTrue($appartenance->getEstAdmin());
        $this->assertSame($dateAjout, $appartenance->getDateAjout());
    }

    public function testSetEstAdminToTrue(): void
    {
        $appartenance = new AppartenanceAdaptor();

        $appartenance->setEstAdmin(true);

        $this->assertTrue($appartenance->getEstAdmin());
    }

    public function testSetEstAdminBackToFalse(): void
    {
        $appartenance = new AppartenanceAdaptor();
        $appartenance->setEstAdmin(true);

        $appartenance->setEstAdmin(false);

        $this->assertFalse($appartenance->getEstAdmin());
    }

    public function testGetDateAjout(): void
    {
        $dateAjout = new DateTime('2026-01-15 10:30:00');
        $appartenance = new AppartenanceAdaptor();
        $appartenance->setDateAjout($dateAjout);

        $this->assertSame($dateAjout, $appartenance->getDateAjout());
    }
}
