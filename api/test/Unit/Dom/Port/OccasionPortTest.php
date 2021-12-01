<?php

declare(strict_types=1);

namespace Test\Unit\Dom\Port;

use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\PasParticipantException;
use App\Dom\Exception\PasParticipantNiAdminException;
use App\Dom\Exception\PasUtilisateurNiAdminException;
use App\Dom\Model\Auth;
use App\Dom\Model\Occasion;
use App\Dom\Model\Resultat;
use App\Dom\Model\Utilisateur;
use App\Dom\Plugin\MailPlugin;
use App\Dom\Port\OccasionPort;
use App\Dom\Repository\ExclusionRepository;
use App\Dom\Repository\OccasionRepository;
use App\Dom\Repository\ResultatRepository;
use DateTime;
use Iterator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class OccasionPortTest extends TestCase
{
    use ProphecyTrait;

   /** @var ObjectProphecy */
    private $mailPluginProphecy;
    /** @var ObjectProphecy */
    private $exclusionRepositoryProphecy;
    /** @var ObjectProphecy */
    private $occasionRepositoryProphecy;
    /** @var ObjectProphecy */
    private $resultatRepositoryProphecy;

    /** @var ObjectProphecy */
    private $authProphecy;
    
    /** @var ObjectProphecy */
    private $utilisateurProphecy;
    
    /** @var ObjectProphecy */
    private $occasionProphecy;
    
    /** @var ObjectProphecy */
    private $resultatProphecy;

    /** @var OccasionPort */
    private $occasionPort;

    public function setUp(): void
    {
        $this->mailPluginProphecy = $this->prophesize(MailPlugin::class);
        $this->exclusionRepositoryProphecy = $this->prophesize(ExclusionRepository::class);
        $this->occasionRepositoryProphecy = $this->prophesize(OccasionRepository::class);
        $this->resultatRepositoryProphecy = $this->prophesize(ResultatRepository::class);

        $this->occasionPort = new OccasionPort(
            $this->mailPluginProphecy->reveal(),
            $this->exclusionRepositoryProphecy->reveal(),
            $this->occasionRepositoryProphecy->reveal(),
            $this->resultatRepositoryProphecy->reveal()
        );

        $this->authProphecy = $this->prophesize(Auth::class);

        $this->utilisateurProphecy = $this->prophesize(Utilisateur::class);

        $this->occasionProphecy = $this->prophesize(Occasion::class);

        $this->resultatProphecy = $this->prophesize(Resultat::class);
    }

    /** @dataProvider provideDataAjouteParticipantOccasion */
    public function testAjouteParticipantOccasion($passee, $envoiEmailReussi = true)
    {
        $participant = $this->utilisateurProphecy->reveal();
        $this->authProphecy->estAdmin()->willReturn(true);

        $occasionAttendue = $this->occasionProphecy->reveal();
        $this->occasionProphecy->addParticipant($participant)->willReturn($occasionAttendue)->shouldBeCalledOnce();
        $this->occasionProphecy->getDate()->willReturn(new DateTime($passee ? 'yesterday' : 'tomorrow'));
        
        $this->occasionRepositoryProphecy->update($occasionAttendue)
            ->willReturn($occasionAttendue)
            ->shouldBeCalledOnce();
        
        if (!$passee) {
            $this->mailPluginProphecy->envoieMailAjoutParticipant($participant, $occasionAttendue)
                ->willReturn($envoiEmailReussi)
                ->shouldBeCalledOnce();
        }
        
        $occasion = $this->occasionPort->ajouteParticipantOccasion(
            $this->authProphecy->reveal(),
            $occasionAttendue,
            $participant
        );

        $this->assertEquals($occasionAttendue, $occasion);
    }

    public function provideDataAjouteParticipantOccasion(): Iterator
    {
        foreach([true, false] as $envoiEmailReussi) {
            yield [false, $envoiEmailReussi];
        }
        yield [true];
    }

    public function testAjouteParticipantOccasionPasAdmin()
    {
        $this->authProphecy->estAdmin()->willReturn(false);

        $this->expectException(PasAdminException::class);
        $this->occasionPort->ajouteParticipantOccasion(
            $this->authProphecy->reveal(),
            $this->occasionProphecy->reveal(),
            $this->utilisateurProphecy->reveal()
        );
    }

    /** @dataProvider provideDataAjouteResultatOccasion */
    public function testAjouteResultatOccasion($passee, $envoiEmailReussi = true)
    {
        $quiOffre = $this->utilisateurProphecy->reveal();
        $this->utilisateurProphecy->estUtilisateur(Argument::not($quiOffre))->willReturn(false);
        $this->utilisateurProphecy->estUtilisateur($quiOffre)->willReturn(true);

        $quiRecoitProphecy = $this->prophesize(Utilisateur::class);
        $quiRecoit = $quiRecoitProphecy->reveal();
        $quiRecoitProphecy->estUtilisateur(Argument::not($quiRecoit))->willReturn(false);
        $quiRecoitProphecy->estUtilisateur($quiRecoit)->willReturn(true);

        $this->authProphecy->estAdmin()->willReturn(true);

        $occasion = $this->occasionProphecy->reveal();
        $this->occasionProphecy->getDate()->willReturn(new DateTime($passee ? 'yesterday' : 'tomorrow'));
        $this->occasionProphecy->getParticipants()->willReturn([
            $quiOffre,
            $quiRecoit,
        ]);

        $resultatAttendu = $this->resultatProphecy->reveal();
        
        $this->resultatRepositoryProphecy->create($occasion, $quiOffre, $quiRecoit)
            ->willReturn($resultatAttendu)
            ->shouldBeCalledOnce();
        
        if (!$passee) {
            $this->mailPluginProphecy->envoieMailTirageFait($quiOffre, $occasion)
                ->willReturn($envoiEmailReussi)
                ->shouldBeCalledOnce();
        }
        
        $resultat = $this->occasionPort->ajouteResultatOccasion(
            $this->authProphecy->reveal(),
            $occasion,
            $quiOffre,
            $quiRecoit
        );

        $this->assertEquals($resultatAttendu, $resultat);
    }

    public function provideDataAjouteResultatOccasion(): Iterator
    {
        foreach([true, false] as $envoiEmailReussi) {
            yield [false, $envoiEmailReussi];
        }
        yield [true];
    }

    public function testAjouteResultatOccasionPasAdmin()
    {
        $quiRecoitProphecy = $this->prophesize(Utilisateur::class);
        $this->authProphecy->estAdmin()->willReturn(false);

        $this->expectException(PasAdminException::class);
        $this->occasionPort->ajouteResultatOccasion(
            $this->authProphecy->reveal(),
            $this->occasionProphecy->reveal(),
            $this->utilisateurProphecy->reveal(),
            $quiRecoitProphecy->reveal()
        );
    }

    /** @dataProvider provideDataAjouteResultatOccasionPasParticipant */
    public function testAjouteResultatOccasionPasParticipant(bool $quiOffreParticipe, bool $quiRecoitParticipe)
    {
        $quiOffre = $this->utilisateurProphecy->reveal();
        $this->utilisateurProphecy->estUtilisateur(Argument::not($quiOffre))->willReturn(false);
        $this->utilisateurProphecy->estUtilisateur($quiOffre)->willReturn(true);

        $quiRecoitProphecy = $this->prophesize(Utilisateur::class);
        $quiRecoit = $quiRecoitProphecy->reveal();
        $quiRecoitProphecy->estUtilisateur(Argument::not($quiRecoit))->willReturn(false);
        $quiRecoitProphecy->estUtilisateur($quiRecoit)->willReturn(true);

        $this->authProphecy->estAdmin()->willReturn(true);

        $occasion = $this->occasionProphecy->reveal();
        $participants = [];
        if ($quiOffreParticipe) $participants[] = $quiOffre;
        if ($quiRecoitParticipe) $participants[] = $quiRecoit;
        $this->occasionProphecy->getParticipants()->willReturn($participants);

        $this->expectException(PasParticipantException::class);
        $this->occasionPort->ajouteResultatOccasion(
            $this->authProphecy->reveal(),
            $occasion,
            $quiOffre,
            $quiRecoit
        );
    }

    public function provideDataAjouteResultatOccasionPasParticipant(): Iterator
    {
        yield([true, false]);
        yield([false, true]);
        yield([false, false]);
    }

    public function testCreeOccasion()
    {
        $this->authProphecy->estAdmin()->willReturn(true);

        $date = new DateTime('3 months');
        $titre = 'nouvelle occasion';

        $this->occasionProphecy->setDate(Argument::cetera())->shouldNotBeCalled();
        $this->occasionProphecy->setTitre(Argument::cetera())->shouldNotBeCalled();
        $occasionAttendue = $this->occasionProphecy->reveal();
        
        $this->occasionRepositoryProphecy->create($date, $titre)
            ->willReturn($occasionAttendue)
            ->shouldBeCalledOnce();
        
        $occasion = $this->occasionPort->creeOccasion(
            $this->authProphecy->reveal(),
            $date,
            $titre
        );

        $this->assertEquals($occasionAttendue, $occasion);
    }

    public function testCreeOccasionPasAdmin()
    {
        $this->authProphecy->estAdmin()->willReturn(false);

        $this->expectException(PasAdminException::class);
        $this->occasionPort->creeOccasion(
            $this->authProphecy->reveal(),
            new DateTime('3 months'),
            'nouvelle occasion'
        );
    }

    /** @dataProvider provideDataGetOccasion */
    public function testGetOccasion(bool $admin, bool $avecResultat)
    {
        $utilisateur = $this->utilisateurProphecy->reveal();
        $quiOffre = $this->prophesize(Utilisateur::class)->reveal();
        
        $this->authProphecy->estAdmin()->willReturn($admin);
        $this->authProphecy->estUtilisateur($utilisateur)->willReturn(!$admin);
        $this->authProphecy->estUtilisateur($quiOffre)->willReturn($avecResultat);
        
        $occasionAttendue = $this->occasionProphecy->reveal();
        $this->occasionProphecy->getParticipants()->willReturn([$utilisateur]);
        
        $resultat = $this->resultatProphecy->reveal();
        $this->resultatProphecy->getQuiOffre()->willReturn($quiOffre);

        $this->resultatRepositoryProphecy->readByOccasion($occasionAttendue)->willReturn([$resultat]);
        
        $resultatsAttendus = $avecResultat ? [$resultat] : [];

        $occasion = $this->occasionPort->getOccasion(
            $this->authProphecy->reveal(),
            $occasionAttendue,
            $resultats
        );

        $this->assertEquals($occasionAttendue, $occasion);
        $this->assertEquals($resultatsAttendus, $resultats);
    }

    public function provideDataGetOccasion(): Iterator
    {
        foreach([true, false] as $admin) {
            foreach([true, false] as $avecResultat) {
                yield([$admin, $avecResultat]);
            }
        }
    }

    public function testGetOccasionPasParticipantNiAdmin() {
        $utilisateur = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(false);
        $this->authProphecy->estUtilisateur($utilisateur)->willReturn(false);

        $occasionAttendue = $this->occasionProphecy->reveal();
        $this->occasionProphecy->getParticipants()->willReturn([$utilisateur]);

        $this->expectException(PasParticipantNiAdminException::class);
        $this->occasionPort->getOccasion(
            $this->authProphecy->reveal(),
            $occasionAttendue,
            $resultats
        );
    }

    public function testListeOccasions()
    {
        $this->authProphecy->estAdmin()->willReturn(true);

        $occasionsAttendues = [
            $this->occasionProphecy->reveal(),
        ];

        $this->occasionRepositoryProphecy->readAll()->willReturn($occasionsAttendues);

        $occasions = $this->occasionPort->listeOccasions(
            $this->authProphecy->reveal()
        );

        $this->assertEquals($occasionsAttendues, $occasions);
    }

    public function testListeOccasionsPasAdmin() {
        $this->authProphecy->estAdmin()->willReturn(false);

        $this->expectException(PasAdminException::class);
        $this->occasionPort->listeOccasions(
            $this->authProphecy->reveal()
        );
    }

    /**
     * @dataProvider provideDataListeOccasionsParticipant
     */
    public function testListeOccasionsParticipant(bool $admin)
    {
        $participant = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estUtilisateur($participant)->willReturn(!$admin);
        $this->authProphecy->estAdmin()->willReturn($admin);

        $occasionsAttendues = [
            $this->occasionProphecy->reveal(),
        ];

        $this->occasionRepositoryProphecy->readByParticipant($participant)->willReturn($occasionsAttendues);

        $occasions = $this->occasionPort->listeOccasionsParticipant(
            $this->authProphecy->reveal(),
            $participant
        );

        $this->assertEquals($occasionsAttendues, $occasions);
    }

    public function provideDataListeOccasionsParticipant(): Iterator
    {
        foreach ([false, true] as $admin) {
            yield [$admin];
        }
    }

    public function testListeOccasionsParticipantPasUtilisateur()
    {
        $participant = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estUtilisateur($participant)->willReturn(false);
        $this->authProphecy->estAdmin()->willReturn(false);

        $this->expectException(PasUtilisateurNiAdminException::class);
        $this->occasionPort->listeOccasionsParticipant(
            $this->authProphecy->reveal(),
            $participant
        );
    }

    public function testModifieOccasion()
    {
        $this->authProphecy->estAdmin()->willReturn(true);

        $date = new DateTime('3 months');
        $titre = 'nouvelle occasion';

        $occasionAttendue = $this->occasionProphecy->reveal();
        $this->occasionProphecy->setDate($date)->willReturn($occasionAttendue)->shouldBeCalledOnce();
        $this->occasionProphecy->setTitre($titre)->willReturn($occasionAttendue)->shouldBeCalledOnce();

        $this->occasionRepositoryProphecy->update($occasionAttendue)
            ->willReturn($occasionAttendue)
            ->shouldBeCalledOnce();

        $occasion = $this->occasionPort->modifieOccasion(
            $this->authProphecy->reveal(),
            $occasionAttendue,
            [
                'date' => $date,
                'titre' => $titre,
            ]
        );

        $this->assertEquals($occasionAttendue, $occasion);
    }

    public function testModifieOccasionPasAdmin()
    {
        $this->authProphecy->estAdmin()->willReturn(false);

        $this->expectException(PasAdminException::class);
        $this->occasionPort->modifieOccasion(
            $this->authProphecy->reveal(),
            $this->occasionProphecy->reveal(),
            [
                'date' => new DateTime('3 months'),
                'titre' => 'nouvelle occasion',
            ]
        );
    }
}
