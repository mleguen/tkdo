<?php

declare(strict_types=1);

namespace Test\Int;

use App\Dom\Model\PrefNotifIdees;
use DateTime;
use Iterator;

class IdeeIntTest extends IntTestCase
{
    /** @dataProvider provideCurl */
    public function testCasNominal(bool $curl): void
    {
        $idee = $this->creeIdeeEnMemoire();
        $auteur = $idee->getAuteur();
        $utilisateur = $idee->getUtilisateur();

        // Occasion à laquelle participe l'utilisateur
        $participantANotifierI = $this->creeUtilisateurEnBase('participantANotifierI', [
            'prefNotifIdees' => PrefNotifIdees::Instantanee
        ]);
        $participantANotifierQ = $this->creeUtilisateurEnBase('participantANotifierQ', [
            'prefNotifIdees' => PrefNotifIdees::Quotidienne,
            'dateDerniereNotifPeriodique' => new DateTime('yesterday')
        ]);
        $this->creeOccasionEnBase(['participants' => [
            $utilisateur,
            $participantANotifierI,
            $participantANotifierQ,
        ]]);

        // Occasion à laquelle ne participe pas l'utilisateur
        $this->creeOccasionEnBase([
            'titre' => 'Autre occasion',
            'participants' => [
                $this->creeUtilisateurEnBase('participantPasANotifierI', [
                    'prefNotifIdees' => PrefNotifIdees::Instantanee
                ]),
                $this->creeUtilisateurEnBase('participantPasANotifierQ', [
                    'prefNotifIdees' => PrefNotifIdees::Quotidienne,
                    'dateDerniereNotifPeriodique' => new DateTime('yesterday')
                ]),
            ]
        ]);

        $this->postConnexion($curl, $auteur);

        // Crée une idée
        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $utilisateur->getId(),
                'description' => $idee->getDescription(),
                'idAuteur' => $auteur->getId(),
            ]
        );
        $this->assertEquals(200, $statusCode);
        $this->assertArrayHasKey('id', $body);
        $idee->setId($body['id']);

        // Vérifie que l'auteur peut voir l'idée
        $this->requestApi(
            $curl,
            'GET',
            '/idee',
            $statusCode,
            $body,
            "idUtilisateur={$utilisateur->getId()}&supprimees=0"
        );
        $this->assertEquals(200, $statusCode);

        $this->assertArraySubset([
            'utilisateur' => self::utilisateurAttendu($utilisateur)
        ], $body);

        $this->assertArrayHasKey('idees', $body);
        $this->assertEquals(1, count($body['idees']));
        $this->assertArraySubset([
            'id' => $idee->getId(),
            'auteur' => self::utilisateurAttendu($auteur),
            'description' => $idee->getDescription(),
        ], $body['idees'][0]);
        $this->assertArrayHasKey('dateProposition', $body['idees'][0]);
        $this->assertArrayNotHasKey('dateSuppression', $body['idees'][0]);

        // Mais pas l'utilisateur
        $this->postConnexion($curl, $utilisateur);
        $this->requestApi(
            $curl,
            'GET',
            '/idee',
            $statusCode,
            $body,
            "idUtilisateur={$utilisateur->getId()}&supprimees=0"
        );
        $this->assertEquals(200, $statusCode);

        $this->assertEquals([
            'utilisateur' => self::utilisateurAttendu($utilisateur),
            'idees' => [],
        ], $body);
        $this->postConnexion($curl, $auteur);

        // Vérifie que seul l'utilisateur à notifier instantanément a bien été notifié de la création
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($participantANotifierI->getEmail(), $emailsRecus[0]);
        $this->assertEquals("Nouvelle idée de cadeau pour {$utilisateur->getNom()}", $emailsRecus[0]->subject);
        $this->assertEquals(1, preg_match('/> ([^\r\n]*)/', $emailsRecus[0]->body, $matches));
        $this->assertEquals($idee->getDescription(), $matches[1]);

        // Marque l'idée comme supprimée
        $this->requestApi(
            $curl,
            'POST',
            "/idee/{$idee->getId()}/suppression",
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);
        $this->assertArrayHasKey('dateSuppression', $body);

        // Vérifie que l'auteur non plus ne voit plus l'idée
        $this->requestApi(
            $curl,
            'GET',
            '/idee',
            $statusCode,
            $body,
            "idUtilisateur={$utilisateur->getId()}&supprimees=0"
        );
        $this->assertEquals(200, $statusCode);

        $this->assertEquals([
            'utilisateur' => self::utilisateurAttendu($utilisateur),
            'idees' => [],
        ], $body);

        // Vérifie que seul l'utilisateur à notifier instantanément a bien été notifié de la suppression
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($participantANotifierI->getEmail(), $emailsRecus[0]);
        $this->assertEquals("Idée de cadeau supprimée pour {$utilisateur->getNom()}", $emailsRecus[0]->subject);
        $this->assertEquals(1, preg_match('/> ([^\r\n]*)/', $emailsRecus[0]->body, $matches));
        $this->assertEquals($idee->getDescription(), $matches[1]);

        // Crée une 2ème idée en base pour qu'elle apparaisse dans les notifications quotidiennes
        $this->creeIdeeEnBase(['auteur' => $auteur, 'utilisateur' => $utilisateur]);

        // Lance les notifications quotidiennes
        exec("php bin/notif-quotidienne.php", $output, $return_var);
        $this->assertMatchesRegularExpression("/NotifCommande: {$participantANotifierQ->getNom()} 2/", implode("\n", $output));
        $this->assertEquals(0, $return_var);

        // Vérifie que seul l'utilisateur à notifier quotidiennement
        // a bien été notifié de la la suppression de la 1ère idée, et de la création de la 2ème
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($participantANotifierQ->getEmail(), $emailsRecus[0]);
        $this->assertEquals('Actualités Tkdo', $emailsRecus[0]->subject);
        $this->assertMatchesRegularExpression("/Une nouvelle idée de cadeau a été proposée pour {$utilisateur->getNom()}/", $emailsRecus[0]->body);
        $this->assertMatchesRegularExpression("/L'idée de cadeau pour {$utilisateur->getNom()} ci-dessous a été retirée de sa liste/", $emailsRecus[0]->body);

        // Lance à nouveau les notifications quotidiennes et vérifie que cette fois aucun mail n'est parti
        $output = [];
        exec("php bin/notif-quotidienne.php", $output, $return_var);
        $this->assertEquals(0, $return_var);
        $this->assertDoesNotMatchRegularExpression("/NotifCommande: {$participantANotifierQ->getNom()}/", implode("\n", $output));
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(0, $emailsRecus);
    }

    /** @dataProvider provideCurl */
    function testCreeIdeeNonAuthentifie(bool $curl)
    {
        $idee = $this->creeIdeeEnMemoire();
        $auteur = $idee->getAuteur();
        $utilisateur = $idee->getUtilisateur();

        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $utilisateur->getId(),
                'description' => $idee->getDescription(),
                'idAuteur' => $auteur->getId(),
            ]
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testCreeIdeeUtilisateurInconnu(bool $curl) {
        $idee = $this->creeIdeeEnMemoire([
            'utilisateur' => $this->creeUtilisateurEnMemoire('utilisateurInconnu'),
        ]);
        $auteur = $idee->getAuteur();
        $idUtilisateur = $auteur->getId() + 1;

        $this->postConnexion($curl, $auteur);

        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $idUtilisateur,
                'description' => 'nouvelle idée',
                'idAuteur' => $auteur->getId(),
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => 'utilisateur inconnu',
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testCreeIdeePasLAuteur(bool $curl) {
        $idee = $this->creeIdeeEnMemoire();
        $auteur = $idee->getAuteur();
        $utilisateur = $idee->getUtilisateur();

        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $utilisateur->getId(),
                'description' => 'nouvelle idée',
                'idAuteur' => $auteur->getId(),
            ]
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est ni l'auteur de l'idée, ni un administrateur",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testListeIdeeNonAuthentifie(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        $this->requestApi(
            $curl,
            'GET',
            '/idee',
            $statusCode,
            $body,
            "idUtilisateur={$utilisateur->getId()}&supprimees=0"
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    /** @dataProvider provideDataListeIdeePasAdmin */
    function testListeIdeePasAdmin(bool $curl, ?int $supprimees)
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'GET',
            '/idee',
            $statusCode,
            $body,
            "idUtilisateur={$utilisateur->getId()}" . (is_null($supprimees) ? '' : "&supprimees=$supprimees")
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est pas un administrateur",
        ], $body);
    }

    function provideDataListeIdeePasAdmin(): Iterator
    {
        foreach($this->provideCurl() as $data) {
            foreach([1, null] as $supprimees) {
                yield array_merge($data, [$supprimees]);
            }
        }
    }

    /** @dataProvider provideCurl */
    function testListeIdeeUtilisateurInconnu(bool $curl)
    {
        $connecte = $this->postConnexion($curl);
        $idUtilisateur = $connecte->getId() + 1;

        $this->requestApi(
            $curl,
            'GET',
            '/idee',
            $statusCode,
            $body,
            "idUtilisateur=$idUtilisateur&supprimees=0"
        );
        $this->assertEquals(404, $statusCode);
        $this->assertEquals([
            'message' => 'utilisateur inconnu',
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testMarqueIdeeCommeSupprimeeNonAuthentifie(bool $curl)
    {
        $idee = $this->creeIdeeEnBase();

        $this->requestApi(
            $curl,
            'POST',
            "/idee/{$idee->getId()}/suppression",
            $statusCode,
            $body
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testMarqueIdeeCommeSupprimeeInconnue(bool $curl)
    {
        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'POST',
            "/idee/99/suppression",
            $statusCode,
            $body
        );
        $this->assertEquals(404, $statusCode);
        $this->assertEquals([
            'message' => 'idée inconnue',
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testMarqueIdeeCommeSupprimeeDejaSupprimee(bool $curl)
    {
        $idee = $this->creeIdeeEnBase(['dateSuppression' => new DateTime()]);

        $this->postConnexion($curl, $idee->getAuteur());

        $this->requestApi(
            $curl,
            'POST',
            "/idee/{$idee->getId()}/suppression",
            $statusCode,
            $body
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => "l'idée a déjà été marquée comme supprimée",
        ], $body);
    }
}
