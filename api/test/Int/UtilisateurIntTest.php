<?php

declare(strict_types=1);

namespace Test\Int;

/**
 * User management workflow integration test
 *
 * Tests the complete user lifecycle from creation through password management.
 * Error cases (401, 403, 404, validation) are tested in ErrorHandlingIntTest.
 */
class UtilisateurIntTest extends IntTestCase
{
    /**
     * Test complete user management workflow
     *
     * Verifies: create user → receive email → login → reset password → change password → become admin
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testUserManagementWorkflow(bool $curl): void
    {
        $admin = $this->utilisateur()->withIdentifiant('admin')->withAdmin()->persist(self::$em);
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->build();

        // Admin creates user
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

        // Verify user appears in user list
        $this->requestApi($curl, 'GET', '/utilisateur', $statusCode, $body);
        $this->assertEquals(200, $statusCode);
        $this->assertEquals(array_map([self::class, 'utilisateurAttendu'], [$admin, $utilisateur]), $body);

        // Verify admin can view user's complete profile
        $this->requestApi($curl, 'GET', "/utilisateur/{$utilisateur->getId()}", $statusCode, $body);
        $this->assertEquals(200, $statusCode);
        $this->assertEquals(array_merge(self::utilisateurAttendu($utilisateur), [
            'admin' => $utilisateur->getAdmin(),
            'email' => $utilisateur->getEmail(),
            'identifiant' => $utilisateur->getIdentifiant(),
            'prefNotifIdees' => $utilisateur->getPrefNotifIdees(),
        ]), $body);

        // Verify user received email with password
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($utilisateur->getEmail(), $emailsRecus[0]);
        $this->assertEquals("Création de votre compte", $emailsRecus[0]->subject);
        $this->assertEquals(1, preg_match('/- identifiant : ([^\r\n]*)/', $emailsRecus[0]->body, $matches));
        $this->assertEquals($utilisateur->getIdentifiant(), $matches[1]);
        $this->assertEquals(1, preg_match('/- mot de passe : ([^\r\n]*)/', $emailsRecus[0]->body, $matches));
        $utilisateur->setMdpClair($matches[1]);

        // User can login with credentials from email
        $this->postConnexion($curl, $utilisateur);

        // Admin resets user's password
        $this->postConnexion($curl, $admin);
        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/{$utilisateur->getId()}/reinitmdp",
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);

        // User receives new password via email
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($utilisateur->getEmail(), $emailsRecus[0]);
        $this->assertEquals("Réinitialisation de votre mot de passe", $emailsRecus[0]->subject);
        $this->assertEquals(1, preg_match('/- mot de passe : ([^\r\n]*)/', $emailsRecus[0]->body, $matches));
        $utilisateur->setMdpClair($matches[1]);

        // User can login with new password
        $this->postConnexion($curl, $utilisateur);

        // User changes their own password and username
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

        // User can login with new credentials
        $this->postConnexion($curl, $utilisateur);

        // Admin promotes user to admin
        $this->postConnexion($curl, $admin);
        $utilisateur->setAdmin(true);
        $this->requestApi(
            $curl,
            'PUT',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body,
            '',
            ['admin' => $utilisateur->getAdmin()]
        );
        $this->assertEquals(200, $statusCode);

        // Verify promoted user can now create other users (admin privilege)
        $this->postConnexion($curl, $utilisateur);
        $autreUtilisateur = $this->utilisateur()->withIdentifiant('autreUtilisateur')->build();
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
}
