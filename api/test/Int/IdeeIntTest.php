<?php

declare(strict_types=1);

namespace Test\Int;

use App\Appli\ModelAdaptor\IdeeAdaptor;
use App\Dom\Model\PrefNotifIdees;
use DateTime;
use Iterator;

class IdeeIntTest extends IntTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testCreeIdeeNonAuthentifie(bool $curl)
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testCreeIdeeUtilisateurInconnu(bool $curl) {
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testCreeIdeePasLAuteur(bool $curl) {
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testListeIdeeNonAuthentifie(bool $curl)
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataListeIdeePasAdmin')]
    public function testListeIdeePasAdmin(bool $curl, ?int $supprimees)
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

    public static function provideDataListeIdeePasAdmin(): Iterator
    {
        foreach(self::provideCurl() as $data) {
            foreach([1, null] as $supprimees) {
                yield array_merge($data, [$supprimees]);
            }
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testListeIdeeUtilisateurInconnu(bool $curl)
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testMarqueIdeeCommeSupprimeeNonAuthentifie(bool $curl)
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testMarqueIdeeCommeSupprimeeInconnue(bool $curl)
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testMarqueIdeeCommeSupprimeeDejaSupprimee(bool $curl)
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testListeIdeeAvecSupprimees(bool $curl)
    {
        $auteur = $this->creeUtilisateurEnBase('auteur', ['admin' => true]);
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        // Crée une idée active et une idée supprimée
        $ideeActive = $this->creeIdeeEnBase([
            'auteur' => $auteur,
            'utilisateur' => $utilisateur,
            'description' => 'idée active',
        ]);
        $ideeSupprimee = $this->creeIdeeEnBase([
            'auteur' => $auteur,
            'utilisateur' => $utilisateur,
            'description' => 'idée supprimée',
            'dateSuppression' => new DateTime(),
        ]);

        $this->postConnexion($curl, $auteur);

        // Vérifie que supprimees=0 ne retourne que l'idée active
        $this->requestApi(
            $curl,
            'GET',
            '/idee',
            $statusCode,
            $body,
            "idUtilisateur={$utilisateur->getId()}&supprimees=0"
        );
        $this->assertEquals(200, $statusCode);
        $this->assertCount(1, $body['idees']);
        $this->assertEquals($ideeActive->getId(), $body['idees'][0]['id']);
        $this->assertArrayNotHasKey('dateSuppression', $body['idees'][0]);

        // Vérifie que supprimees=1 retourne uniquement les idées supprimées (nécessite d'être admin)
        $this->requestApi(
            $curl,
            'GET',
            '/idee',
            $statusCode,
            $body,
            "idUtilisateur={$utilisateur->getId()}&supprimees=1"
        );
        $this->assertEquals(200, $statusCode);
        $this->assertCount(1, $body['idees']);
        $this->assertEquals($ideeSupprimee->getId(), $body['idees'][0]['id']);
        $this->assertArrayHasKey('dateSuppression', $body['idees'][0]);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testAdminPeutVoirToutesLesIdees(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $auteur = $this->creeUtilisateurEnBase('auteur');
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        // Crée une idée dont l'admin n'est pas l'auteur
        $idee = $this->creeIdeeEnBase([
            'auteur' => $auteur,
            'utilisateur' => $utilisateur,
            'description' => 'idée pour test admin',
        ]);

        $this->postConnexion($curl, $admin);

        // Vérifie que l'admin peut voir l'idée même s'il n'est pas l'auteur
        $this->requestApi(
            $curl,
            'GET',
            '/idee',
            $statusCode,
            $body,
            "idUtilisateur={$utilisateur->getId()}&supprimees=0"
        );
        $this->assertEquals(200, $statusCode);
        $this->assertCount(1, $body['idees']);
        $this->assertEquals($idee->getId(), $body['idees'][0]['id']);
        $this->assertEquals($auteur->getId(), $body['idees'][0]['auteur']['id']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testAdminPeutCreerIdeePourNimporteQuelUtilisateur(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $auteur = $this->creeUtilisateurEnBase('auteur');
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        $this->postConnexion($curl, $admin);

        // L'admin crée une idée pour un utilisateur, avec un auteur différent
        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $utilisateur->getId(),
                'description' => 'idée créée par admin',
                'idAuteur' => $auteur->getId(),
            ]
        );
        $this->assertEquals(200, $statusCode);
        $this->assertArrayHasKey('id', $body);

        // Vérifie que l'idée a bien été créée avec le bon auteur
        $idIdee = $body['id'];
        $this->postConnexion($curl, $auteur);
        $this->requestApi(
            $curl,
            'GET',
            '/idee',
            $statusCode,
            $body,
            "idUtilisateur={$utilisateur->getId()}&supprimees=0"
        );
        $this->assertEquals(200, $statusCode);
        $this->assertCount(1, $body['idees']);
        $this->assertEquals($idIdee, $body['idees'][0]['id']);
        $this->assertEquals($auteur->getId(), $body['idees'][0]['auteur']['id']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testVerificationEtatBaseDonneesApresCreation(bool $curl)
    {
        $auteur = $this->creeUtilisateurEnBase('auteur');
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $description = 'vérification état BDD';

        $this->postConnexion($curl, $auteur);

        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $utilisateur->getId(),
                'description' => $description,
                'idAuteur' => $auteur->getId(),
            ]
        );
        $this->assertEquals(200, $statusCode);
        $idIdee = $body['id'];

        // Vérifie directement dans la base de données
        $ideeEnBase = self::$em->find(IdeeAdaptor::class, $idIdee);
        $this->assertNotNull($ideeEnBase);
        $this->assertEquals($description, $ideeEnBase->getDescription());
        $this->assertEquals($auteur->getId(), $ideeEnBase->getAuteur()->getId());
        $this->assertEquals($utilisateur->getId(), $ideeEnBase->getUtilisateur()->getId());
        $this->assertNotNull($ideeEnBase->getDateProposition());
        $this->assertNull($ideeEnBase->getDateSuppression());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testVerificationEtatBaseDonneesApresSuppression(bool $curl)
    {
        $idee = $this->creeIdeeEnBase();
        $auteur = $idee->getAuteur();

        $this->postConnexion($curl, $auteur);

        $this->requestApi(
            $curl,
            'POST',
            "/idee/{$idee->getId()}/suppression",
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);

        // Vérifie directement dans la base de données
        self::$em->clear(); // Clear cache to force reload
        $ideeEnBase = self::$em->find(IdeeAdaptor::class, $idee->getId());
        $this->assertNotNull($ideeEnBase);
        $this->assertNotNull($ideeEnBase->getDateSuppression());
        $this->assertInstanceOf(DateTime::class, $ideeEnBase->getDateSuppression());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testCreeIdeeDescriptionVide(bool $curl)
    {
        $auteur = $this->creeUtilisateurEnBase('auteur');
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        $this->postConnexion($curl, $auteur);

        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $utilisateur->getId(),
                'description' => '',
                'idAuteur' => $auteur->getId(),
            ]
        );

        // Une description vide devrait être acceptée (c'est au front de valider si nécessaire)
        $this->assertEquals(200, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testCreeIdeeSansIdAuteur(bool $curl)
    {
        $auteur = $this->creeUtilisateurEnBase('auteur');
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        $this->postConnexion($curl, $auteur);

        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $utilisateur->getId(),
                'description' => 'test sans auteur',
            ]
        );

        // Devrait échouer car l'idAuteur est obligatoire
        $this->assertNotEquals(200, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testListeIdeeUtilisateurSansIdees(bool $curl)
    {
        $auteur = $this->creeUtilisateurEnBase('auteur');
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        $this->postConnexion($curl, $auteur);

        // Récupère les idées d'un utilisateur qui n'en a aucune
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
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testPlusieursIdeesMemeUtilisateur(bool $curl)
    {
        $auteur1 = $this->creeUtilisateurEnBase('auteur1');
        $auteur2 = $this->creeUtilisateurEnBase('auteur2');
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        // Crée plusieurs idées pour le même utilisateur par différents auteurs
        $idee1 = $this->creeIdeeEnBase([
            'auteur' => $auteur1,
            'utilisateur' => $utilisateur,
            'description' => 'première idée',
        ]);
        $idee2 = $this->creeIdeeEnBase([
            'auteur' => $auteur2,
            'utilisateur' => $utilisateur,
            'description' => 'deuxième idée',
        ]);
        $idee3 = $this->creeIdeeEnBase([
            'auteur' => $auteur1,
            'utilisateur' => $utilisateur,
            'description' => 'troisième idée',
        ]);

        $this->postConnexion($curl, $auteur1);

        // Tous les auteurs peuvent voir toutes les idées pour un utilisateur (sauf l'utilisateur lui-même)
        $this->requestApi(
            $curl,
            'GET',
            '/idee',
            $statusCode,
            $body,
            "idUtilisateur={$utilisateur->getId()}&supprimees=0"
        );
        $this->assertEquals(200, $statusCode);
        $this->assertCount(3, $body['idees']);

        // Vérifie que les 3 idées sont présentes avec les bons auteurs
        $ids = array_column($body['idees'], 'id');
        $this->assertContains($idee1->getId(), $ids);
        $this->assertContains($idee2->getId(), $ids);
        $this->assertContains($idee3->getId(), $ids);

        // Vérifie que chaque idée a le bon auteur
        foreach ($body['idees'] as $idee) {
            if ($idee['id'] === $idee1->getId() || $idee['id'] === $idee3->getId()) {
                $this->assertEquals($auteur1->getId(), $idee['auteur']['id']);
            } elseif ($idee['id'] === $idee2->getId()) {
                $this->assertEquals($auteur2->getId(), $idee['auteur']['id']);
            }
        }

        // L'utilisateur ne doit pas voir ses propres idées
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
        $this->assertCount(0, $body['idees']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testMarqueIdeeCommeSupprimeeParAdmin(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $auteur = $this->creeUtilisateurEnBase('auteur');
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        $idee = $this->creeIdeeEnBase([
            'auteur' => $auteur,
            'utilisateur' => $utilisateur,
        ]);

        $this->postConnexion($curl, $admin);

        // L'admin doit pouvoir supprimer une idée dont il n'est pas l'auteur
        $this->requestApi(
            $curl,
            'POST',
            "/idee/{$idee->getId()}/suppression",
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);
        $this->assertArrayHasKey('dateSuppression', $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testMarqueIdeeCommeSupprimeeParNonAuteurNonAdmin(bool $curl)
    {
        $auteur = $this->creeUtilisateurEnBase('auteur');
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $tiersPas = $this->creeUtilisateurEnBase('tiers');

        $idee = $this->creeIdeeEnBase([
            'auteur' => $auteur,
            'utilisateur' => $utilisateur,
        ]);

        $this->postConnexion($curl, $tiersPas);

        // Un tiers ne doit pas pouvoir supprimer une idée
        $this->requestApi(
            $curl,
            'POST',
            "/idee/{$idee->getId()}/suppression",
            $statusCode,
            $body
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est ni l'auteur de l'idée, ni un administrateur",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testNotificationInstantaneeSeulementParticipantsOccasion(bool $curl)
    {
        $auteur = $this->creeUtilisateurEnBase('auteur');
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        // Participant dans la même occasion
        $participantDansOccasion = $this->creeUtilisateurEnBase('participantDansOccasion', [
            'prefNotifIdees' => PrefNotifIdees::Instantanee,
        ]);

        // Participant pas dans la même occasion
        $participantPasDansOccasion = $this->creeUtilisateurEnBase('participantPasDansOccasion', [
            'prefNotifIdees' => PrefNotifIdees::Instantanee,
        ]);

        // Crée une occasion avec utilisateur et participantDansOccasion
        $this->creeOccasionEnBase([
            'participants' => [$utilisateur, $participantDansOccasion],
        ]);

        // Crée une autre occasion avec participantPasDansOccasion
        $this->creeOccasionEnBase([
            'titre' => 'Autre occasion',
            'participants' => [$participantPasDansOccasion],
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
                'description' => 'test notification',
                'idAuteur' => $auteur->getId(),
            ]
        );
        $this->assertEquals(200, $statusCode);

        // Vérifie que seul le participant dans la même occasion a été notifié
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($participantDansOccasion->getEmail(), $emailsRecus[0]);
    }
}
