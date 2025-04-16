<?php

declare(strict_types=1);

namespace Test\Int;

use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use DateTime;
use DateTimeInterface;
use Iterator;

class OccasionIntTest extends IntTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testCasNominal(bool $curl): void
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $occasion = $this->creeOccasionEnMemoire();

        $this->postConnexion($curl, $admin);

        // Crée une occasion
        $this->requestApi(
            $curl,
            'POST',
            '/occasion',
            $statusCode,
            $body,
            '',
            [
                'date' => $occasion->getDate()->format(DateTimeInterface::W3C),
                'titre' => $occasion->getTitre(),
            ]
        );
        $this->assertEquals(200, $statusCode);
        $this->assertArrayHasKey('id', $body);
        $occasion->setId($body['id']);
        $this->assertEquals(self::occasionAttendue($occasion), $body);

        // Vérifie qu'elle apparaît dans la liste des occasions
        $this->requestApi(
            $curl,
            'GET',
            '/occasion',
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);
        $this->assertEquals([self::occasionAttendue($occasion)], $body);

        // La modifie
        $occasion->setDate(new DateTime('tomorrow'));
        $occasion->setTitre('demain');
        $this->requestApi(
            $curl,
            'PUT',
            "/occasion/{$occasion->getId()}",
            $statusCode,
            $body,
            '',
            [
                'date' => $occasion->getDate()->format(DateTimeInterface::W3C),
                'titre' => $occasion->getTitre(),
            ]
        );
        $this->assertEquals(200, $statusCode);
        $this->assertEquals(self::occasionAttendue($occasion), $body);

        // Ajoute des participants
        $nbParticipants = 3;
        /** @var UtilisateurAdaptor */
        $participantPrecedent = null;
        for ($idx = 0; $idx <= $nbParticipants; $idx++) {
            if ($idx < $nbParticipants) {
                // Ajoute un participant
                $participant = $this->creeUtilisateurEnBase("participant$idx");
                $this->requestApi(
                    $curl,
                    'POST',
                    "/occasion/{$occasion->getId()}/participant",
                    $statusCode,
                    $body,
                    '',
                    [
                        'idParticipant' => $participant->getId(),
                    ]
                );
                $this->assertEquals(200, $statusCode);
                $this->assertEquals(self::utilisateurAttendu($participant), $body);
                $occasion->addParticipant($participant);
    
                // Vérifie qu'il a reçu un email pour le prévenir
                $emailsRecus = $this->depileDerniersEmailsRecus();
                $this->assertCount(1, $emailsRecus);
                $this->assertMessageRecipientsContains($participant->getEmail(), $emailsRecus[0]);
                $this->assertEquals("Participation au tirage cadeaux {$occasion->getTitre()}", $emailsRecus[0]->subject);
    
                // Vérifie que l'occasion apparaît dans la liste des occasions du participant
                $this->postConnexion($curl, $participant);
                $this->requestApi(
                    $curl,
                    'GET',
                    '/occasion',
                    $statusCode,
                    $body,
                    "idParticipant={$participant->getId()}"
                );
                $this->assertEquals(200, $statusCode);
                $this->assertEquals([self::occasionAttendue($occasion)], $body);

                // Et qu'il peut l'afficher
                $this->requestApi(
                    $curl,
                    'GET',
                    "/occasion/{$occasion->getId()}",
                    $statusCode,
                    $body
                );
                $this->assertEquals(200, $statusCode);
                $this->assertEquals(self::occasionDetailleeAttendue($occasion), $body);

                $this->postConnexion($curl, $admin);
            } else {
                $participant = $occasion->getParticipants()[0];
            }

            if ($idx > 0) {
                // Ajoute un résultat
                $resultat = $this->creeResultatEnMemoire($occasion, $participant, $participantPrecedent);
                $this->requestApi(
                    $curl,
                    'POST',
                    "/occasion/{$resultat->getOccasion()->getId()}/resultat",
                    $statusCode,
                    $body,
                    '',
                    [
                        'idQuiOffre' => $resultat->getQuiOffre()->getId(),
                        'idQuiRecoit' => $resultat->getQuiRecoit()->getId(),
                    ]
                );
                $this->assertEquals(200, $statusCode);
                $this->assertEquals(self::resultatAttendu($resultat), $body);

                // Vérifie que le participant a reçu un email pour le prévenir
                $emailsRecus = $this->depileDerniersEmailsRecus();
                $this->assertCount(1, $emailsRecus);
                $this->assertMessageRecipientsContains($participant->getEmail(), $emailsRecus[0]);
                $this->assertEquals("Tirage au sort fait pour {$occasion->getTitre()}", $emailsRecus[0]->subject);

                // Et qu'il voit le resultat quand il affiche l'occasion
                $this->postConnexion($curl, $participant);
                $this->requestApi(
                    $curl,
                    'GET',
                    "/occasion/{$occasion->getId()}",
                    $statusCode,
                    $body
                );
                $this->assertEquals(200, $statusCode);
                $this->assertEquals(self::occasionDetailleeAttendue($occasion, [$resultat]), $body);

                $this->postConnexion($curl, $admin);
            }
            $participantPrecedent = $participant;
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testAjouteParticipantOccasionNonAuthentifie(bool $curl)
    {
        $participant = $this->creeUtilisateurEnBase('participant');
        $occasion = $this->creeOccasionEnBase();

        $this->requestApi(
            $curl,
            'POST',
            "/occasion/{$occasion->getId()}/participant",
            $statusCode,
            $body,
            '',
            [
                'idParticipant' => $participant->getId(),
            ]
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testAjouteParticipantOccasionPasAdmin(bool $curl)
    {
        $participant = $this->creeUtilisateurEnBase('participant');
        $occasion = $this->creeOccasionEnBase();

        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'POST',
            "/occasion/{$occasion->getId()}/participant",
            $statusCode,
            $body,
            '',
            [
                'idParticipant' => $participant->getId(),
            ]
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est pas un administrateur",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testAjouteParticipantOccasionInconnue(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $participant = $this->creeUtilisateurEnBase('participant');
        $occasion = $this->creeOccasionEnBase();
        $idOccasionInconnue = $occasion->getId() + 1;

        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'POST',
            "/occasion/$idOccasionInconnue/participant",
            $statusCode,
            $body,
            '',
            [
                'idParticipant' => $participant->getId(),
            ]
        );
        $this->assertEquals(404, $statusCode);
        $this->assertEquals([
            'message' => 'occasion inconnue',
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testAjouteParticipantOccasionUtilisateurInconnu(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $idParticipantInconnu = $admin->getId() + 1;
        $occasion = $this->creeOccasionEnBase();

        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'POST',
            "/occasion/{$occasion->getId()}/participant",
            $statusCode,
            $body,
            '',
            [
                'idParticipant' => $idParticipantInconnu,
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => 'utilisateur inconnu',
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testAjouteParticipantOccasion2Fois(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $participant = $this->creeUtilisateurEnBase('participant');
        $occasion = $this->creeOccasionEnBase(['participants' => [$participant]]);

        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'POST',
            "/occasion/{$occasion->getId()}/participant",
            $statusCode,
            $body,
            '',
            [
                'idParticipant' => $participant->getId(),
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur participe déjà à l'occasion",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testAjouteResultatOccasionNonAuthentifie(bool $curl)
    {
        $quiOffre = $this->creeUtilisateurEnBase('quiOffre');
        $quiRecoit = $this->creeUtilisateurEnBase('quiRecoit');
        $occasion = $this->creeOccasionEnBase();

        $this->requestApi(
            $curl,
            'POST',
            "/occasion/{$occasion->getId()}/resultat",
            $statusCode,
            $body,
            '',
            [
                'idQuiOffre' => $quiOffre->getId(),
                'idQuiRecoit' => $quiRecoit->getId(),
            ]
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testAjouteResultatOccasionPasAdmin(bool $curl)
    {
        $quiOffre = $this->creeUtilisateurEnBase('quiOffre');
        $quiRecoit = $this->creeUtilisateurEnBase('quiRecoit');
        $occasion = $this->creeOccasionEnBase();

        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'POST',
            "/occasion/{$occasion->getId()}/resultat",
            $statusCode,
            $body,
            '',
            [
                'idQuiOffre' => $quiOffre->getId(),
                'idQuiRecoit' => $quiRecoit->getId(),
            ]
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est pas un administrateur",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testAjouteResultatOccasionInconnue(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $quiOffre = $this->creeUtilisateurEnBase('quiOffre');
        $quiRecoit = $this->creeUtilisateurEnBase('quiRecoit');
        $occasion = $this->creeOccasionEnBase();
        $idOccasionInconnue = $occasion->getId() + 1;

        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'POST',
            "/occasion/$idOccasionInconnue/resultat",
            $statusCode,
            $body,
            '',
            [
                'idQuiOffre' => $quiOffre->getId(),
                'idQuiRecoit' => $quiRecoit->getId(),
            ]
        );
        $this->assertEquals(404, $statusCode);
        $this->assertEquals([
            'message' => 'occasion inconnue',
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataAjouteResultatOccasionUtilisateurInconnu')]
    public function testAjouteResultatOccasionUtilisateurInconnu(bool $curl, bool $quiOffreInconnu, bool $quiRecoitInconnu)
    {
        $quiOffre = $this->creeUtilisateurEnBase('quiOffre');
        $quiRecoit = $this->creeUtilisateurEnBase('quiRecoit');
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $idUtilisateurInconnu = $admin->getId() + 1;

        $idQuiOffre = $quiOffreInconnu ? $idUtilisateurInconnu++ : $quiOffre->getId();
        $idQuiRecoit = $quiRecoitInconnu ? $idUtilisateurInconnu++ : $quiRecoit->getId();
        $occasion = $this->creeOccasionEnBase();

        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'POST',
            "/occasion/{$occasion->getId()}/resultat",
            $statusCode,
            $body,
            '',
            [
                'idQuiOffre' => $idQuiOffre,
                'idQuiRecoit' => $idQuiRecoit,
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => 'utilisateur inconnu',
        ], $body);
    }

    public static function provideDataAjouteResultatOccasionUtilisateurInconnu(): Iterator
    {
        foreach(self::provideCurl() as $data) {
            yield array_merge($data, [true, false]);
            yield array_merge($data, [false, true]);
            yield array_merge($data, [true, true]);
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataAjouteResultatOccasion2Fois')]
    public function testAjouteResultatOccasion2Fois(bool $curl, bool $quiOffreDeja, bool $quiRecoitDeja)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $quiOffre = $this->creeUtilisateurEnBase('quiOffre');
        $quiRecoit = $this->creeUtilisateurEnBase('quiRecoit');
        $autreParticipant = $this->creeUtilisateurEnBase('autreParticipant');
        $occasion = $this->creeOccasionEnBase(['participants' => [
            $quiOffre,
            $quiRecoit,
            $autreParticipant,
        ]]);

        if ($quiOffreDeja) $this->creeResultatEnBase($occasion, $quiOffre, $autreParticipant);
        if ($quiRecoitDeja) $this->creeResultatEnBase($occasion, $autreParticipant, $quiRecoit);

        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'POST',
            "/occasion/{$occasion->getId()}/resultat",
            $statusCode,
            $body,
            '',
            [
                'idQuiOffre' => $quiOffre->getId(),
                'idQuiRecoit' => $quiRecoit->getId(),
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => "l'un des utilisateurs offre ou reçoit déjà pour cette occasion",
        ], $body);
    }

    public static function provideDataAjouteResultatOccasion2Fois(): Iterator
    {
        foreach (self::provideCurl() as $data) {
            yield array_merge($data, [true, false]);
            yield array_merge($data, [false, true]);
            yield array_merge($data, [true, true]);
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testCreeOccasionNonAuthentifie(bool $curl)
    {
        $occasion = $this->creeOccasionEnMemoire();

        $this->requestApi(
            $curl,
            'POST',
            '/occasion',
            $statusCode,
            $body,
            '',
            [
                'date' => $occasion->getDate()->format(DateTimeInterface::W3C),
                'titre' => $occasion->getTitre(),
            ]
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testCreeOccasionPasAdmin(bool $curl) {
        $occasion = $this->creeOccasionEnMemoire();

        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'POST',
            '/occasion',
            $statusCode,
            $body,
            '',
            [
                'date' => $occasion->getDate()->format(DateTimeInterface::W3C),
                'titre' => $occasion->getTitre(),
            ]
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est pas un administrateur",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testGetOccasionNonAuthentifie(bool $curl)
    {
        $occasion = $this->creeOccasionEnBase();

        $this->requestApi(
            $curl,
            'GET',
            "/occasion/{$occasion->getId()}",
            $statusCode,
            $body
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testGetOccasionPasParticipantNiAdmin(bool $curl)
    {
        $occasion = $this->creeOccasionEnBase();
        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'GET',
            "/occasion/{$occasion->getId()}",
            $statusCode,
            $body
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié ne participe pas à l'occasion et n'est pas un administrateur",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testGetOccasionInconnue(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $occasion = $this->creeOccasionEnBase();
        $idOccasionInconnue = $occasion->getId() + 1;

        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'GET',
            "/occasion/$idOccasionInconnue",
            $statusCode,
            $body
        );
        $this->assertEquals(404, $statusCode);
        $this->assertEquals([
            'message' => 'occasion inconnue',
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testListeOccasionsNonAuthentifie(bool $curl)
    {
        $this->requestApi(
            $curl,
            'GET',
            '/occasion',
            $statusCode,
            $body
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testListeOccasionsPasAdmin(bool $curl)
    {
        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'GET',
            '/occasion',
            $statusCode,
            $body
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est pas un administrateur",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testListeOccasionsParticipantNonAuthentifie(bool $curl)
    {
        $participant = $this->creeUtilisateurEnBase('participant');

        $this->requestApi(
            $curl,
            'GET',
            '/occasion',
            $statusCode,
            $body,
            "idParticipant={$participant->getId()}"
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testListeOccasionsParticipantPasUtilisateur(bool $curl)
    {
        $participant = $this->creeUtilisateurEnBase('participant');
        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'GET',
            '/occasion',
            $statusCode,
            $body,
            "idParticipant={$participant->getId()}"
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est ni l'utilisateur lui-même, ni un administrateur",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testListeOccasionsParticipantUtilisateurInconnu(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $idParticipant = $admin->getId() + 1;

        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'GET',
            '/occasion',
            $statusCode,
            $body,
            "idParticipant=$idParticipant"
        );
        $this->assertEquals(404, $statusCode);
        $this->assertEquals([
            'message' => 'utilisateur inconnu',
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testListeOccasionsTiers(bool $curl)
    {
        $participant = $this->creeUtilisateurEnBase('participant');
        $this->creeOccasionEnBase(['participants' => [$participant]]);

        $tiers = $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'GET',
            '/occasion',
            $statusCode,
            $body,
            "idParticipant={$tiers->getId()}"
        );
        $this->assertEquals(200, $statusCode);
        $this->assertEquals([], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testModifieOccasionNonAuthentifie(bool $curl)
    {
        $occasion = $this->creeOccasionEnBase();

        $this->requestApi(
            $curl,
            'PUT',
            "/occasion/{$occasion->getId()}",
            $statusCode,
            $body,
            '',
            []
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testModifieOccasionPasAdmin(bool $curl)
    {
        $occasion = $this->creeOccasionEnBase();

        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'PUT',
            "/occasion/{$occasion->getId()}",
            $statusCode,
            $body,
            '',
            []
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est pas un administrateur",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testModifieOccasionInconnue(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $occasion = $this->creeOccasionEnBase();
        $idOccasionInconnue = $occasion->getId() + 1;

        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'PUT',
            "/occasion/$idOccasionInconnue",
            $statusCode,
            $body,
            '',
            []
        );
        $this->assertEquals(404, $statusCode);
        $this->assertEquals([
            'message' => 'occasion inconnue',
        ], $body);
    }
}
