<?php

declare(strict_types=1);

namespace Test\Int;

class UtilisateurIntTest extends IntTestCase
{
    /** @dataProvider provideCurl */
    public function testCasNominal(bool $curl): void
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $utilisateur = $this->creeUtilisateurEnMemoire('utilisateur');
       
        // Crée un utilisateur
        $this->postConnexion($curl, $admin);
        $this->requestApi(
            $curl,
            'POST',
            '/utilisateur',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'email' => $utilisateur->getEmail(),
                'nom' => $utilisateur->getNom(),
                'genre' => $utilisateur->getGenre(),
            ]
        );
        $this->assertEquals(200, $statusCode);
        $this->assertArrayHasKey('id', $body);
        $utilisateur->setId($body['id']);

        // Vérifie qu'il apparaît dans la liste des utilisateurs
        $this->requestApi(
            $curl,
            'GET',
            '/utilisateur',
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);
        $this->assertEquals(array_map([self::class, 'utilisateurAttendu'], [
            $admin,
            $utilisateur,
        ]), $body);

        // Et qu'un admin peut afficher son profil compplet
        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);
        $this->assertEquals(array_merge(self::utilisateurAttendu($utilisateur), [
            'admin' => $utilisateur->getAdmin(),
            'email' => $utilisateur->getEmail(),
            'identifiant' => $utilisateur->getIdentifiant(),
            'prefNotifIdees' => $utilisateur->getPrefNotifIdees(),
        ]), $body);

        // Récupère le mail reçu par l'utilisateur contenant son mot de passe
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($utilisateur->getEmail(), $emailsRecus[0]);
        $this->assertEquals("Création de votre compte", $emailsRecus[0]->subject);
        $this->assertEquals(1, preg_match('/- identifiant : ([^\r\n]*)/', $emailsRecus[0]->body, $matches));
        $this->assertEquals($utilisateur->getIdentifiant(), $matches[1]);
        $this->assertEquals(1, preg_match('/- mot de passe : ([^\r\n]*)/', $emailsRecus[0]->body, $matches));
        $utilisateur->setMdpClair($matches[1]);

        // Vérifie que l'utilisateur peut se connecter
        $this->postConnexion($curl, $utilisateur);
        
        // Réinitialise son mot de passe
        $this->postConnexion($curl, $admin);
        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/{$utilisateur->getId()}/reinitmdp",
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);
        $this->assertArraySubset([
            'id' => $utilisateur->getId()
        ], $body);

        // Récupère le mail reçu par l'utilisateur contenant son nouveau mot de passe
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($utilisateur->getEmail(), $emailsRecus[0]);
        $this->assertEquals("Réinitialisation de votre mot de passe", $emailsRecus[0]->subject);
        $this->assertEquals(1, preg_match('/- identifiant : ([^\r\n]*)/', $emailsRecus[0]->body, $matches));
        $this->assertEquals($utilisateur->getIdentifiant(), $matches[1]);
        $this->assertEquals(1, preg_match('/- mot de passe : ([^\r\n]*)/', $emailsRecus[0]->body, $matches));
        $utilisateur->setMdpClair($matches[1]);

        // Vérifie que l'utilisateur peut se connecter avec son nouveau mot de passe
        $this->postConnexion($curl, $utilisateur);

        // Qu'il peut lui-même modifier son identifiant et son mot de passe
        $utilisateur->setIdentifiant($utilisateur->getIdentifiant() . '2');
        $utilisateur->setMdpClair('nouveaumdp');
        $this->requestApi(
            $curl,
            'PUT',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'mdp' => $utilisateur->getMdpClair(),
            ]
        );
        $this->assertEquals(200, $statusCode);
        $this->assertArraySubset([
            'id' => $utilisateur->getId()
        ], $body);

        // Et qu'il arrive bien à se connecter avec ces nouveaux identifiants ensuite
        $this->postConnexion($curl, $utilisateur);

        // Fait devenir admin l'utilisateur
        $this->postConnexion($curl, $admin);
        $utilisateur->setAdmin(true);
        $this->requestApi(
            $curl,
            'PUT',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body,
            '',
            [
                'admin' => $utilisateur->getAdmin(),
            ]
        );
        $this->assertEquals(200, $statusCode);
        $this->assertArraySubset([
            'id' => $utilisateur->getId()
        ], $body);

        // Vérifie qu'il peut maintenant créer lui-même un autre utilisateur
        $this->postConnexion($curl, $utilisateur);
        $autreUtilisateur = $this->creeUtilisateurEnMemoire('autreUtilisateur');
        $this->requestApi(
            $curl,
            'POST',
            '/utilisateur',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $autreUtilisateur->getIdentifiant(),
                'email' => $autreUtilisateur->getEmail(),
                'nom' => $autreUtilisateur->getNom(),
                'genre' => $autreUtilisateur->getGenre(),
            ]
        );
        $this->assertEquals(200, $statusCode);
        $this->depileDerniersEmailsRecus();
    }

    /** @dataProvider provideCurl */
    function testCreeUtilisateurNonAuthentifie(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnMemoire('utilisateur');

        $this->requestApi(
            $curl,
            'POST',
            '/utilisateur',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'email' => $utilisateur->getEmail(),
                'nom' => $utilisateur->getNom(),
                'genre' => $utilisateur->getGenre(),
            ]
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testCreeUtilisateurPasAdmin(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnMemoire('utilisateur');
        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'POST',
            '/utilisateur',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'email' => $utilisateur->getEmail(),
                'nom' => $utilisateur->getNom(),
                'genre' => $utilisateur->getGenre(),
            ]
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est pas un administrateur",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testCreeUtilisateurDoublonIdentifiant(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'POST',
            '/utilisateur',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'email' => $utilisateur->getEmail(),
                'nom' => $utilisateur->getNom(),
                'genre' => $utilisateur->getGenre(),
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => 'identifiant déjà utilisé',
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testCreeUtilisateurEmailInvalide(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $utilisateur = $this->creeUtilisateurEnMemoire('utilisateur');

        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'POST',
            '/utilisateur',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'email' => 'pas-un-email',
                'nom' => $utilisateur->getNom(),
                'genre' => $utilisateur->getGenre(),
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => "pas-un-email n'est pas un email valide",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testCreeUtilisateurPrefNotifIdeesInvalide(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $utilisateur = $this->creeUtilisateurEnMemoire('utilisateur');

        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'POST',
            '/utilisateur',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'email' => $utilisateur->getEmail(),
                'nom' => $utilisateur->getNom(),
                'genre' => $utilisateur->getGenre(),
                'prefNotifIdees' => 'invalide',
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => 'format de préférence de notification incorrect',
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testCreeUtilisateurGenreInvalide(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $utilisateur = $this->creeUtilisateurEnMemoire('utilisateur');

        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'POST',
            '/utilisateur',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'email' => $utilisateur->getEmail(),
                'nom' => $utilisateur->getNom(),
                'genre' => 'invalide',
                'prefNotifIdees' => $utilisateur->getPrefNotifIdees(),
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => 'genre invalide',
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testResetMdpUtilisateurNonAuthentifie(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/{$utilisateur->getId()}/reinitmdp",
            $statusCode,
            $body
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testResetMdpUtilisateurPasAdmin(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/{$utilisateur->getId()}/reinitmdp",
            $statusCode,
            $body
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est pas un administrateur",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testResetMdpUtilisateurUtilisateurInconnu(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $idUtilisateur = $admin->getId()+1;
        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/$idUtilisateur/reinitmdp",
            $statusCode,
            $body
        );
        $this->assertEquals(404, $statusCode);
        $this->assertEquals([
            'message' => 'utilisateur inconnu',
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testModifieUtilisateurNonAuthentifie(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        $this->requestApi(
            $curl,
            'PUT',
            "/utilisateur/{$utilisateur->getId()}",
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

    /** @dataProvider provideCurl */
    function testModifieUtilisateurUtilisateurInconnu(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $idUtilisateur = $admin->getId() + 1;
        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'PUT',
            "/utilisateur/$idUtilisateur",
            $statusCode,
            $body,
            '',
            []
        );
        $this->assertEquals(404, $statusCode);
        $this->assertEquals([
            'message' => 'utilisateur inconnu',
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testModifieUtilisateurPasUtilisateur(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'PUT',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body,
            '',
            []
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est ni l'utilisateur lui-même, ni un administrateur",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testModifieUtilisateurPasAdmin(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $this->postConnexion($curl, $utilisateur);

        $this->requestApi(
            $curl,
            'PUT',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body,
            '',
            [
                'admin' => true,
            ]
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est pas un administrateur",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testModifieUtilisateurModificationMdpInterdite(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'PUT',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body,
            '',
            [
                'mdp' => 'nouveaumdp',
            ]
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "seul l'utilisateur lui-même peut modifier son mot de passe",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testModifieUtilisateurDoublonIdentifiant(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $autreUtilisateur = $this->creeUtilisateurEnBase('autreUtilisateur');
        $this->postConnexion($curl, $utilisateur);

        $this->requestApi(
            $curl,
            'PUT',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $autreUtilisateur->getIdentifiant(),
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => "identifiant déjà utilisé",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testModifieUtilisateurEmailInvalide(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $this->postConnexion($curl, $utilisateur);

        $this->requestApi(
            $curl,
            'PUT',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body,
            '',
            [
                'email' => 'pas-un-email',
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => "pas-un-email n'est pas un email valide",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testModifieUtilisateurPrefNotifIdeesInvalide(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $this->postConnexion($curl, $utilisateur);

        $this->requestApi(
            $curl,
            'PUT',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body,
            '',
            [
                'prefNotifIdees' => 'invalide',
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => 'format de préférence de notification incorrect',
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testModifieUtilisateurGenreInvalide(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $this->postConnexion($curl, $utilisateur);

        $this->requestApi(
            $curl,
            'PUT',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body,
            '',
            [
                'genre' => 'invalide',
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => 'genre invalide',
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testListeUtilisateursPasAdmin(bool $curl)
    {
        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'GET',
            '/utilisateur',
            $statusCode,
            $body
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est pas un administrateur",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testGetUtilisateurNonAuthentifie(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testGetUtilisateurPasUtilisateur(bool $curl)
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $this->postConnexion($curl);

        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est ni l'utilisateur lui-même, ni un administrateur",
        ], $body);
    }

    /** @dataProvider provideCurl */
    function testGetUtilisateurUtilisateurInconnu(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $idUtilisateur = $admin->getId() + 1;
        $this->postConnexion($curl, $admin);

        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/$idUtilisateur",
            $statusCode,
            $body
        );
        $this->assertEquals(404, $statusCode);
        $this->assertEquals([
            'message' => 'utilisateur inconnu',
        ], $body);
    }
}
