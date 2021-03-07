<?php

declare(strict_types=1);

namespace Test\Unit\Dom\Port;

use App\Dom\Exception\EmailInvalideException;
use App\Dom\Exception\GenreInvalideException;
use App\Dom\Exception\ModificationMdpInterditeException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\PasUtilisateurNiAdminException;
use App\Dom\Exception\PrefNotifIdeesInvalideException;
use App\Dom\Model\Auth;
use App\Dom\Model\Genre;
use App\Dom\Model\PrefNotifIdees;
use App\Dom\Model\Utilisateur;
use App\Dom\Plugin\MailPlugin;
use App\Dom\Plugin\PasswordPlugin;
use App\Dom\Port\UtilisateurPort;
use App\Dom\Repository\UtilisateurRepository;
use Iterator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class UtilisateurPortTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy */
    private $mailPluginProphecy;
    /** @var ObjectProphecy */
    private $passwordPluginProphecy;
    /** @var ObjectProphecy */
    private $utilisateurRepositoryProphecy;

    /** @var ObjectProphecy */
    private $authProphecy;
    
    /** @var ObjectProphecy */
    private $utilisateurProphecy;
    
    /** @var UtilisateurPort */
    private $utilisateurPort;

    public function setUp(): void
    {
        $this->mailPluginProphecy = $this->prophesize(MailPlugin::class);
        $this->passwordPluginProphecy = $this->prophesize(PasswordPlugin::class);
        $this->utilisateurRepositoryProphecy = $this->prophesize(UtilisateurRepository::class);

        $this->utilisateurPort = new UtilisateurPort(
            $this->mailPluginProphecy->reveal(),
            $this->passwordPluginProphecy->reveal(),
            $this->utilisateurRepositoryProphecy->reveal()
        );

        $this->authProphecy = $this->prophesize(Auth::class);

        $this->utilisateurProphecy = $this->prophesize(Utilisateur::class);
    }

    /** @dataProvider provideDataTestCreeUtilisateur */
    public function testCreeUtilisateur(bool $admin)
    {
        $utilisateurAttendu = $this->utilisateurProphecy->reveal();
        
        $this->authProphecy->estAdmin()->willReturn(true);

        $identifiant = 'utilisateur';
        $email = 'utilisateur@localhost';
        $mdp = 'mdputilisateur';
        $nom = 'Utilisateur';
        $genre = Genre::Feminin;
        $prefNotifIdees = PrefNotifIdees::Aucune;

        $testCase = $this;

        $this->passwordPluginProphecy->randomPassword()
            ->willReturn($mdp)
            ->shouldBeCalledOnce();

        $this->utilisateurRepositoryProphecy->create(Argument::cetera())
            ->will(function ($args) use ($testCase, $identifiant, $email, $mdp, $nom, $genre, $admin, $prefNotifIdees, $utilisateurAttendu) {
                $testCase->assertEquals($identifiant, $args[0]);
                $testCase->assertEquals($email, $args[1]);
                $testCase->assertEquals($mdp, $args[2]);
                $testCase->assertEquals($nom, $args[3]);
                $testCase->assertEquals($genre, $args[4]);
                $testCase->assertEquals($admin, $args[5]);
                $testCase->assertEquals($prefNotifIdees, $args[6]);

                return $utilisateurAttendu;
            })
            ->shouldBeCalledOnce();
        
        $this->mailPluginProphecy->envoieMailMdpCreation(Argument::cetera())
            ->will(function ($args) use ($testCase, $utilisateurAttendu, $mdp) {
                $testCase->assertEquals($utilisateurAttendu, $args[0]);
                $testCase->assertEquals($mdp, $args[1]);
                return true;
            })
            ->shouldBeCalledOnce();
        
        $utilisateur = $this->utilisateurPort->creeUtilisateur(
            $this->authProphecy->reveal(),
            $identifiant,
            $email,
            $nom,
            $genre,
            $admin,
            $prefNotifIdees
        );

        $this->assertEquals($utilisateurAttendu, $utilisateur);
    }

    public function provideDataTestCreeUtilisateur(): array
    {
        return [
            ['admin' => false],
            ['admin' => true],
        ];
    }

    public function testCreeUtilisateurPasAdmin()
    {
        $this->authProphecy->estAdmin()->willReturn(false);

        $this->expectException(PasAdminException::class);
        $this->utilisateurPort->creeUtilisateur(
            $this->authProphecy->reveal(),
            'utilisateur',
            'utilisateur@localhost',
            'Utilisateur',
            Genre::Feminin,
            false,
            PrefNotifIdees::Aucune
        );
    }

    public function testCreeUtilisateurEmailInvalide()
    {
        $this->authProphecy->estAdmin()->willReturn(true);

        $this->expectException(EmailInvalideException::class);
        $this->utilisateurPort->creeUtilisateur(
            $this->authProphecy->reveal(),
            'utilisateur',
            'emailinvalide',
            'Utilisateur',
            Genre::Feminin,
            false,
            PrefNotifIdees::Aucune
        );
    }

    public function testCreeUtilisateurPrefNotifIdeesInvalide()
    {
        $this->authProphecy->estAdmin()->willReturn(true);

        $this->expectException(PrefNotifIdeesInvalideException::class);
        $this->utilisateurPort->creeUtilisateur(
            $this->authProphecy->reveal(),
            'utilisateur',
            'utilisateur@localhost',
            'Utilisateur',
            Genre::Feminin,
            false,
            'prefnotifideesinvalide'
        );
    }

    public function testCreeUtilisateurGenreInvalide()
    {
        $this->authProphecy->estAdmin()->willReturn(true);

        $this->expectException(GenreInvalideException::class);
        $this->utilisateurPort->creeUtilisateur(
            $this->authProphecy->reveal(),
            'utilisateur',
            'utilisateur@localhost',
            'Utilisateur',
            'genreinvalide',
            false,
            PrefNotifIdees::Aucune
        );
    }

    public function testReinitMdpUtilisateur()
    {
        $utilisateurAttendu = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(true);

        $mdp = 'nouveaumdputilisateur';

        $testCase = $this;

        $this->passwordPluginProphecy->randomPassword()
            ->willReturn($mdp)
            ->shouldBeCalledOnce();

        $this->utilisateurProphecy->setMdpClair(Argument::cetera())
            ->will(function ($args) use ($testCase, $mdp, $utilisateurAttendu) {
                $testCase->assertEquals($mdp, $args[0]);
                return $utilisateurAttendu;
            })
            ->shouldBeCalledOnce();

        $this->utilisateurRepositoryProphecy->update(Argument::cetera())
            ->will(function ($args) use ($testCase, $utilisateurAttendu) {
                $testCase->assertEquals($utilisateurAttendu, $args[0]);
                return $utilisateurAttendu;
            })
            ->shouldBeCalledOnce();

        $this->mailPluginProphecy->envoieMailMdpReinitialisation(Argument::cetera())
            ->will(function ($args) use ($testCase, $utilisateurAttendu, $mdp) {
                $testCase->assertEquals($utilisateurAttendu, $args[0]);
                $testCase->assertEquals($mdp, $args[1]);
                return true;
            })
            ->shouldBeCalledOnce();

        $utilisateur = $this->utilisateurPort->reinitMdpUtilisateur(
            $this->authProphecy->reveal(),
            $utilisateurAttendu
        );

        $this->assertEquals($utilisateurAttendu, $utilisateur);
    }

    public function testReinitMdpUtilisateurPasAdmin()
    {
        $this->authProphecy->estAdmin()->willReturn(false);

        $this->expectException(PasAdminException::class);
        $this->utilisateurPort->reinitMdpUtilisateur(
            $this->authProphecy->reveal(),
            $this->utilisateurProphecy->reveal()
        );
    }

    /** @dataProvider provideDataTestModifieUtilisateur */
    public function testModifieUtilisateur(bool $admin)
    {
        $utilisateurAttendu = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn($admin);
        $this->authProphecy->estUtilisateur($utilisateurAttendu)->willReturn(!$admin);

        $identifiant = 'utilisateur';
        $email = 'utilisateur@localhost';
        $mdp = 'mdputilisateur';
        $devientAdmin = true;
        $nom = 'Utilisateur';
        $genre = Genre::Feminin;
        $prefNotifIdees = PrefNotifIdees::Quotidienne;

        $this->utilisateurProphecy->getAdmin()->willReturn(!$devientAdmin);
        $this->utilisateurProphecy->getPrefNotifIdees()->willReturn(PrefNotifIdees::Aucune);

        $this->utilisateurProphecy->setIdentifiant($identifiant)->willReturn($utilisateurAttendu)->shouldBeCalledOnce();
        $this->utilisateurProphecy->setEmail($email)->willReturn($utilisateurAttendu)->shouldBeCalledOnce();
        if ($admin) {
            $this->utilisateurProphecy->setAdmin($devientAdmin)->willReturn($utilisateurAttendu)->shouldBeCalledOnce();
            $this->utilisateurProphecy->setMdpClair(Argument::cetera())->shouldNotBeCalled();
        } else {
            $this->utilisateurProphecy->setAdmin(Argument::cetera())->shouldNotBeCalled();
            $this->utilisateurProphecy->setMdpClair($mdp)->willReturn($utilisateurAttendu)->shouldBeCalledOnce();
        }
        $this->utilisateurProphecy->setNom($nom)->willReturn($utilisateurAttendu)->shouldBeCalledOnce();
        $this->utilisateurProphecy->setGenre($genre)->willReturn($utilisateurAttendu)->shouldBeCalledOnce();
        $this->utilisateurProphecy->setPrefNotifIdees($prefNotifIdees)->willReturn($utilisateurAttendu)->shouldBeCalledOnce();
        $this->utilisateurProphecy->setDateDerniereNotifPeriodique(Argument::cetera())->willReturn($utilisateurAttendu);

        $this->utilisateurRepositoryProphecy->update($utilisateurAttendu)->willReturn($utilisateurAttendu)->shouldBeCalledOnce();

        $modifications = [
            'identifiant' => $identifiant,
            'email' => $email,
            'nom' => $nom,
            'genre' => $genre,
            'prefNotifIdees' => $prefNotifIdees,
        ];
        if ($admin) {
            $modifications['admin'] = $devientAdmin;
        } else {
            $modifications['mdp'] = $mdp;
        }

        $utilisateur = $this->utilisateurPort->modifieUtilisateur(
            $this->authProphecy->reveal(),
            $utilisateurAttendu,
            $modifications
        );

        $this->assertEquals($utilisateurAttendu, $utilisateur);
    }

    public function provideDataTestModifieUtilisateur(): Iterator
    {
        foreach([false, true] as $admin) {
            yield [$admin];
        }
    }

    public function testModifieUtilisateurPasUtilisateur()
    {
        $utilisateur = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(false);
        $this->authProphecy->estUtilisateur($utilisateur)->willReturn(false);

        $this->expectException(PasUtilisateurNiAdminException::class);
        $this->utilisateurPort->modifieUtilisateur(
            $this->authProphecy->reveal(),
            $utilisateur,
            []
        );
    }

    public function testModifieUtilisateurPasAdmin()
    {
        $utilisateur = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(false);
        $this->authProphecy->estUtilisateur($utilisateur)->willReturn(true);

        $this->utilisateurProphecy->getAdmin()->willReturn(false);

        $this->expectException(PasAdminException::class);
        $this->utilisateurPort->modifieUtilisateur(
            $this->authProphecy->reveal(),
            $utilisateur,
            [
                'admin' => true,
            ]
        );
    }

    public function testModifieUtilisateurModificationMdpInterdite()
    {
        $utilisateur = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(true);
        $this->authProphecy->estUtilisateur($utilisateur)->willReturn(false);

        $this->expectException(ModificationMdpInterditeException::class);
        $this->utilisateurPort->modifieUtilisateur(
            $this->authProphecy->reveal(),
            $utilisateur,
            [
                'mdp' => 'mdputilisateur',
            ]
        );
    }

    public function testModifieUtilisateurEmailInvalide()
    {
        $utilisateur = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(false);
        $this->authProphecy->estUtilisateur($utilisateur)->willReturn(true);

        $this->expectException(EmailInvalideException::class);
        $this->utilisateurPort->modifieUtilisateur(
            $this->authProphecy->reveal(),
            $utilisateur,
            [
                'email' => 'emailinvalide',
            ]
        );
    }

    public function testModifieUtilisateurPrefNotifIdeesInvalide()
    {
        $utilisateur = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(false);
        $this->authProphecy->estUtilisateur($utilisateur)->willReturn(true);

        $this->expectException(PrefNotifIdeesInvalideException::class);
        $this->utilisateurPort->modifieUtilisateur(
            $this->authProphecy->reveal(),
            $utilisateur,
            [
                'prefNotifIdees' => 'prefnotifideesinvalide',
            ]
        );
    }

    public function testModifieUtilisateurGenreInvalide()
    {
        $utilisateur = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(false);
        $this->authProphecy->estUtilisateur($utilisateur)->willReturn(true);

        $this->expectException(GenreInvalideException::class);
        $this->utilisateurPort->modifieUtilisateur(
            $this->authProphecy->reveal(),
            $utilisateur,
            [
                'genre' => 'genreinvalide',
            ]
        );
    }

    public function testListeUtilisateurs()
    {
        $autreUtilisateurProphecy = $this->prophesize(UtilisateurRepository::class);

        $this->authProphecy->estAdmin()->willReturn(true);

        $utilisateursAttendus = [
            $this->utilisateurProphecy->reveal(),
            $autreUtilisateurProphecy->reveal()
        ];

        $this->utilisateurRepositoryProphecy->readAll()
            ->willReturn($utilisateursAttendus)
            ->shouldBeCalledOnce();

        $utilisateurs = $this->utilisateurPort->listeUtilisateurs(
            $this->authProphecy->reveal()
        );

        $this->assertEquals($utilisateursAttendus, $utilisateurs);
    }

    public function testListeUtilisateursPasAdmin()
    {
        $this->authProphecy->estAdmin()->willReturn(false);

        $this->expectException(PasAdminException::class);
        $this->utilisateurPort->listeUtilisateurs(
            $this->authProphecy->reveal()
        );
    }

    /** @dataProvider provideDataTestGetUtilisateur */
    public function testGetUtilisateur(bool $admin)
    {
        $utilisateurAttendu = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn($admin);
        $this->authProphecy->estUtilisateur($utilisateurAttendu)->willReturn(!$admin);

        $utilisateur = $this->utilisateurPort->getUtilisateur(
            $this->authProphecy->reveal(),
            $utilisateurAttendu
        );

        $this->assertEquals($utilisateurAttendu, $utilisateur);
    }

    public function provideDataTestGetUtilisateur(): Iterator
    {
        foreach ([false, true] as $admin) {
            yield [$admin];
        }
    }

    public function testGetUtilisateurPasUtilisateur()
    {
        $utilisateurAttendu = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(false);
        $this->authProphecy->estUtilisateur($utilisateurAttendu)->willReturn(false);

        $this->expectException(PasUtilisateurNiAdminException::class);
        $this->utilisateurPort->getUtilisateur(
            $this->authProphecy->reveal(),
            $utilisateurAttendu
        );
    }
}
