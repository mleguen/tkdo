<?php

declare(strict_types=1);

namespace Test\Unit\Dom\Port;

use App\Dom\Exception\OccasionPasseeException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Exception\PasParticipantException;
use App\Dom\Exception\PasParticipantNiAdminException;
use App\Dom\Exception\PasUtilisateurNiAdminException;
use App\Dom\Exception\TirageDejaLanceException;
use App\Dom\Exception\TirageEchoueException;
use App\Dom\Model\Auth;
use App\Dom\Model\Exclusion;
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
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class OccasionPortTest extends UnitTestCase
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataAjouteParticipantOccasion')]
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

    public static function provideDataAjouteParticipantOccasion(): Iterator
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataAjouteResultatOccasion')]
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

    public static function provideDataAjouteResultatOccasion(): Iterator
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataAjouteResultatOccasionPasParticipant')]
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

    public static function provideDataAjouteResultatOccasionPasParticipant(): Iterator
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataGetOccasion')]
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

    public static function provideDataGetOccasion(): Iterator
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataTestAdmin')]
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

    public function testLanceTirage()
    {
        $this->authProphecy->estAdmin()->willReturn(true);

        $occasion = $this->occasionProphecy->reveal();
        $this->occasionProphecy->getDate()->willReturn(new DateTime('tomorrow'));

        // Create 3 participants
        $participant1Prophecy = $this->prophesize(Utilisateur::class);
        $participant1 = $participant1Prophecy->reveal();
        $participant2Prophecy = $this->prophesize(Utilisateur::class);
        $participant2 = $participant2Prophecy->reveal();
        $participant3Prophecy = $this->prophesize(Utilisateur::class);
        $participant3 = $participant3Prophecy->reveal();
        $participants = [$participant1, $participant2, $participant3];
        $this->occasionProphecy->getParticipants()->willReturn($participants);

        // No existing results
        $this->resultatRepositoryProphecy->hasForOccasion($occasion)->willReturn(false);

        // No exclusions
        $this->exclusionRepositoryProphecy->readByParticipantsOccasion($occasion)->willReturn([]);

        // No past results
        $this->resultatRepositoryProphecy->readByParticipantsOccasion($occasion)->willReturn([]);

        // Track created results to verify draw validity
        $createdResults = [];
        $testCase = $this;
        $this->resultatRepositoryProphecy->create($occasion, Argument::type(Utilisateur::class), Argument::type(Utilisateur::class))
            ->will(function ($args) use ($testCase, &$createdResults) {
                $createdResults[] = ['quiOffre' => $args[1], 'quiRecoit' => $args[2]];
                $resultatProphecy = $testCase->prophesize(Resultat::class);
                return $resultatProphecy->reveal();
            })
            ->shouldBeCalledTimes(3);

        // Expect emails to be sent to all participants
        $this->mailPluginProphecy->envoieMailTirageFait($participant1, $occasion)->willReturn(true)->shouldBeCalledOnce();
        $this->mailPluginProphecy->envoieMailTirageFait($participant2, $occasion)->willReturn(true)->shouldBeCalledOnce();
        $this->mailPluginProphecy->envoieMailTirageFait($participant3, $occasion)->willReturn(true)->shouldBeCalledOnce();

        $resultats = $this->occasionPort->lanceTirage(
            $this->authProphecy->reveal(),
            $occasion,
            false,
            10
        );

        // Verify draw validity
        $this->assertCount(3, $resultats);

        // Verify everyone gives exactly once
        $givingParticipants = array_column($createdResults, 'quiOffre');
        $this->assertCount(3, array_unique($givingParticipants, SORT_REGULAR));
        $this->assertEqualsCanonicalizing($participants, $givingParticipants);

        // Verify everyone receives exactly once
        $receivingParticipants = array_column($createdResults, 'quiRecoit');
        $this->assertCount(3, array_unique($receivingParticipants, SORT_REGULAR));
        $this->assertEqualsCanonicalizing($participants, $receivingParticipants);

        // Verify no one gives to themselves
        foreach ($createdResults as $result) {
            $this->assertNotSame($result['quiOffre'], $result['quiRecoit']);
        }
    }

    public function testLanceTirageAvecExclusions()
    {
        $this->authProphecy->estAdmin()->willReturn(true);

        $occasion = $this->occasionProphecy->reveal();
        $this->occasionProphecy->getDate()->willReturn(new DateTime('tomorrow'));

        // Create 3 participants
        $participant1Prophecy = $this->prophesize(Utilisateur::class);
        $participant1 = $participant1Prophecy->reveal();
        $participant2Prophecy = $this->prophesize(Utilisateur::class);
        $participant2 = $participant2Prophecy->reveal();
        $participant3Prophecy = $this->prophesize(Utilisateur::class);
        $participant3 = $participant3Prophecy->reveal();
        $participants = [$participant1, $participant2, $participant3];
        $this->occasionProphecy->getParticipants()->willReturn($participants);

        // No existing results
        $this->resultatRepositoryProphecy->hasForOccasion($occasion)->willReturn(false);

        // Add exclusion: participant1 cannot give to participant2
        $exclusionProphecy = $this->prophesize(Exclusion::class);
        $exclusionProphecy->getQuiOffre()->willReturn($participant1);
        $exclusionProphecy->getQuiNeDoitPasRecevoir()->willReturn($participant2);
        $this->exclusionRepositoryProphecy->readByParticipantsOccasion($occasion)->willReturn([$exclusionProphecy->reveal()]);

        // No past results
        $this->resultatRepositoryProphecy->readByParticipantsOccasion($occasion)->willReturn([]);

        // Track created results to verify exclusion is respected
        $createdResults = [];
        $testCase = $this;
        $this->resultatRepositoryProphecy->create($occasion, Argument::type(Utilisateur::class), Argument::type(Utilisateur::class))
            ->will(function ($args) use ($testCase, &$createdResults) {
                $createdResults[] = ['quiOffre' => $args[1], 'quiRecoit' => $args[2]];
                $resultatProphecy = $testCase->prophesize(Resultat::class);
                return $resultatProphecy->reveal();
            })
            ->shouldBeCalledTimes(3);

        // Expect emails to be sent to all participants
        $this->mailPluginProphecy->envoieMailTirageFait(Argument::type(Utilisateur::class), $occasion)->willReturn(true)->shouldBeCalledTimes(3);

        $resultats = $this->occasionPort->lanceTirage(
            $this->authProphecy->reveal(),
            $occasion,
            false,
            100
        );

        // Verify draw validity
        $this->assertCount(3, $resultats);

        // Verify everyone gives exactly once
        $givingParticipants = array_column($createdResults, 'quiOffre');
        $this->assertCount(3, array_unique($givingParticipants, SORT_REGULAR));
        $this->assertEqualsCanonicalizing($participants, $givingParticipants);

        // Verify everyone receives exactly once
        $receivingParticipants = array_column($createdResults, 'quiRecoit');
        $this->assertCount(3, array_unique($receivingParticipants, SORT_REGULAR));
        $this->assertEqualsCanonicalizing($participants, $receivingParticipants);

        // Verify no one gives to themselves
        foreach ($createdResults as $result) {
            $this->assertNotSame($result['quiOffre'], $result['quiRecoit']);
        }

        // Verify exclusion is respected: participant1 does not give to participant2
        foreach ($createdResults as $result) {
            if ($result['quiOffre'] === $participant1) {
                $this->assertNotSame($participant2, $result['quiRecoit'], 'Exclusion violated: participant1 should not give to participant2');
            }
        }
    }

    public function testLanceTirageAvecResultatsPasses()
    {
        $this->authProphecy->estAdmin()->willReturn(true);

        $occasion = $this->occasionProphecy->reveal();
        $this->occasionProphecy->getDate()->willReturn(new DateTime('tomorrow'));

        // Create 3 participants
        $participant1Prophecy = $this->prophesize(Utilisateur::class);
        $participant1 = $participant1Prophecy->reveal();
        $participant2Prophecy = $this->prophesize(Utilisateur::class);
        $participant2 = $participant2Prophecy->reveal();
        $participant3Prophecy = $this->prophesize(Utilisateur::class);
        $participant3 = $participant3Prophecy->reveal();
        $participants = [$participant1, $participant2, $participant3];
        $this->occasionProphecy->getParticipants()->willReturn($participants);

        // No existing results
        $this->resultatRepositoryProphecy->hasForOccasion($occasion)->willReturn(false);

        // No exclusions
        $this->exclusionRepositoryProphecy->readByParticipantsOccasion($occasion)->willReturn([]);

        // Past result: participant1 gave to participant2 in a past occasion
        $resultatPasseProphecy = $this->prophesize(Resultat::class);
        $resultatPasseProphecy->getQuiOffre()->willReturn($participant1);
        $resultatPasseProphecy->getQuiRecoit()->willReturn($participant2);
        $this->resultatRepositoryProphecy->readByParticipantsOccasion($occasion)->willReturn([$resultatPasseProphecy->reveal()]);

        // Track created results to verify past results are avoided
        $createdResults = [];
        $testCase = $this;
        $this->resultatRepositoryProphecy->create($occasion, Argument::type(Utilisateur::class), Argument::type(Utilisateur::class))
            ->will(function ($args) use ($testCase, &$createdResults) {
                $createdResults[] = ['quiOffre' => $args[1], 'quiRecoit' => $args[2]];
                $resultatProphecy = $testCase->prophesize(Resultat::class);
                return $resultatProphecy->reveal();
            })
            ->shouldBeCalledTimes(3);

        // Expect emails to be sent to all participants
        $this->mailPluginProphecy->envoieMailTirageFait(Argument::type(Utilisateur::class), $occasion)->willReturn(true)->shouldBeCalledTimes(3);

        $resultats = $this->occasionPort->lanceTirage(
            $this->authProphecy->reveal(),
            $occasion,
            false,
            100
        );

        // Verify draw validity
        $this->assertCount(3, $resultats);

        // Verify everyone gives exactly once
        $givingParticipants = array_column($createdResults, 'quiOffre');
        $this->assertCount(3, array_unique($givingParticipants, SORT_REGULAR));
        $this->assertEqualsCanonicalizing($participants, $givingParticipants);

        // Verify everyone receives exactly once
        $receivingParticipants = array_column($createdResults, 'quiRecoit');
        $this->assertCount(3, array_unique($receivingParticipants, SORT_REGULAR));
        $this->assertEqualsCanonicalizing($participants, $receivingParticipants);

        // Verify no one gives to themselves
        foreach ($createdResults as $result) {
            $this->assertNotSame($result['quiOffre'], $result['quiRecoit']);
        }

        // Verify past result is avoided: participant1 does not give to participant2
        foreach ($createdResults as $result) {
            if ($result['quiOffre'] === $participant1) {
                $this->assertNotSame($participant2, $result['quiRecoit'], 'Past result should be avoided: participant1 should not give to participant2 again');
            }
        }
    }

    public function testLanceTirageForce()
    {
        $this->authProphecy->estAdmin()->willReturn(true);

        $occasion = $this->occasionProphecy->reveal();
        $this->occasionProphecy->getDate()->willReturn(new DateTime('tomorrow'));

        $participant1Prophecy = $this->prophesize(Utilisateur::class);
        $participant1 = $participant1Prophecy->reveal();
        $participant2Prophecy = $this->prophesize(Utilisateur::class);
        $participant2 = $participant2Prophecy->reveal();
        $participants = [$participant1, $participant2];
        $this->occasionProphecy->getParticipants()->willReturn($participants);

        // Existing results
        $this->resultatRepositoryProphecy->hasForOccasion($occasion)->willReturn(true);

        // Should delete existing results when force=true
        $this->resultatRepositoryProphecy->deleteByOccasion($occasion)->shouldBeCalledOnce();

        // No exclusions or past results
        $this->exclusionRepositoryProphecy->readByParticipantsOccasion($occasion)->willReturn([]);
        $this->resultatRepositoryProphecy->readByParticipantsOccasion($occasion)->willReturn([]);

        // Track created results to verify draw validity
        $createdResults = [];
        $testCase = $this;
        $this->resultatRepositoryProphecy->create($occasion, Argument::type(Utilisateur::class), Argument::type(Utilisateur::class))
            ->will(function ($args) use ($testCase, &$createdResults) {
                $createdResults[] = ['quiOffre' => $args[1], 'quiRecoit' => $args[2]];
                $resultatProphecy = $testCase->prophesize(Resultat::class);
                return $resultatProphecy->reveal();
            })
            ->shouldBeCalledTimes(2);

        // Expect emails to be sent
        $this->mailPluginProphecy->envoieMailTirageFait(Argument::type(Utilisateur::class), $occasion)->willReturn(true)->shouldBeCalledTimes(2);

        $resultats = $this->occasionPort->lanceTirage(
            $this->authProphecy->reveal(),
            $occasion,
            true,  // force
            10
        );

        // Verify draw validity
        $this->assertCount(2, $resultats);

        // Verify everyone gives exactly once
        $givingParticipants = array_column($createdResults, 'quiOffre');
        $this->assertCount(2, array_unique($givingParticipants, SORT_REGULAR));
        $this->assertEqualsCanonicalizing($participants, $givingParticipants);

        // Verify everyone receives exactly once
        $receivingParticipants = array_column($createdResults, 'quiRecoit');
        $this->assertCount(2, array_unique($receivingParticipants, SORT_REGULAR));
        $this->assertEqualsCanonicalizing($participants, $receivingParticipants);

        // Verify no one gives to themselves (each participant gives to the other)
        foreach ($createdResults as $result) {
            $this->assertNotSame($result['quiOffre'], $result['quiRecoit']);
        }
    }

    public function testLanceTiragePasAdmin()
    {
        $this->authProphecy->estAdmin()->willReturn(false);

        $this->expectException(PasAdminException::class);
        $this->occasionPort->lanceTirage(
            $this->authProphecy->reveal(),
            $this->occasionProphecy->reveal(),
            false,
            10
        );
    }

    public function testLanceTirageOccasionPassee()
    {
        $this->authProphecy->estAdmin()->willReturn(true);

        $occasion = $this->occasionProphecy->reveal();
        $this->occasionProphecy->getDate()->willReturn(new DateTime('yesterday'));

        $this->expectException(OccasionPasseeException::class);
        $this->occasionPort->lanceTirage(
            $this->authProphecy->reveal(),
            $occasion,
            false,
            10
        );
    }

    public function testLanceTirageDejeLance()
    {
        $this->authProphecy->estAdmin()->willReturn(true);

        $occasion = $this->occasionProphecy->reveal();
        $this->occasionProphecy->getDate()->willReturn(new DateTime('tomorrow'));

        // Existing results and force=false
        $this->resultatRepositoryProphecy->hasForOccasion($occasion)->willReturn(true);

        $this->expectException(TirageDejaLanceException::class);
        $this->occasionPort->lanceTirage(
            $this->authProphecy->reveal(),
            $occasion,
            false,  // force=false
            10
        );
    }

    public function testLanceTirageEchoue()
    {
        $this->authProphecy->estAdmin()->willReturn(true);

        $occasion = $this->occasionProphecy->reveal();
        $this->occasionProphecy->getDate()->willReturn(new DateTime('tomorrow'));

        // Create 2 participants with mutual exclusion (impossible draw)
        $participant1Prophecy = $this->prophesize(Utilisateur::class);
        $participant1 = $participant1Prophecy->reveal();
        $participant2Prophecy = $this->prophesize(Utilisateur::class);
        $participant2 = $participant2Prophecy->reveal();
        $participants = [$participant1, $participant2];
        $this->occasionProphecy->getParticipants()->willReturn($participants);

        // No existing results
        $this->resultatRepositoryProphecy->hasForOccasion($occasion)->willReturn(false);

        // Mutual exclusions: participant1 cannot give to participant2 AND participant2 cannot give to participant1
        $exclusion1Prophecy = $this->prophesize(Exclusion::class);
        $exclusion1Prophecy->getQuiOffre()->willReturn($participant1);
        $exclusion1Prophecy->getQuiNeDoitPasRecevoir()->willReturn($participant2);
        $exclusion2Prophecy = $this->prophesize(Exclusion::class);
        $exclusion2Prophecy->getQuiOffre()->willReturn($participant2);
        $exclusion2Prophecy->getQuiNeDoitPasRecevoir()->willReturn($participant1);
        $this->exclusionRepositoryProphecy->readByParticipantsOccasion($occasion)
            ->willReturn([$exclusion1Prophecy->reveal(), $exclusion2Prophecy->reveal()]);

        // No past results
        $this->resultatRepositoryProphecy->readByParticipantsOccasion($occasion)->willReturn([]);

        $this->expectException(TirageEchoueException::class);
        $this->occasionPort->lanceTirage(
            $this->authProphecy->reveal(),
            $occasion,
            false,
            10
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
