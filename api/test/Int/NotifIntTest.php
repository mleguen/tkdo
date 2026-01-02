<?php

declare(strict_types=1);

namespace Test\Int;

use App\Dom\Model\PrefNotifIdees;
use DateTime;

class NotifIntTest extends IntTestCase
{
    /**
     * Test that instant notifications are sent when an idea is created
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testNotificationInstantaneeCreationIdee(bool $curl): void
    {
        $auteur = $this->utilisateur()->withIdentifiant('auteur')->persist(self::$em);
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Participant who wants instant notifications
        $participantInstant = $this->utilisateur()
            ->withIdentifiant('participantInstant')
            ->withPrefNotifIdees(PrefNotifIdees::Instantanee)
            ->persist(self::$em);

        // Create an occasion with the user and the instant notification participant
        $this->occasion()
            ->withParticipants([$utilisateur, $participantInstant])
            ->persist(self::$em);

        $this->postConnexion($curl, $auteur);

        // Create an idea
        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $utilisateur->getId(),
                'description' => 'Test notification',
                'idAuteur' => $auteur->getId(),
            ]
        );
        $this->assertEquals(200, $statusCode);

        // Verify that an email was sent
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($participantInstant->getEmail(), $emailsRecus[0]);
        $this->assertEquals("Nouvelle idée de cadeau pour {$utilisateur->getNom()}", $emailsRecus[0]->subject);
    }

    /**
     * Test that instant notifications are sent when an idea is deleted
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testNotificationInstantaneeSuppressionIdee(bool $curl): void
    {
        $auteur = $this->utilisateur()->withIdentifiant('auteur')->persist(self::$em);
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Participant who wants instant notifications
        $participantInstant = $this->utilisateur()
            ->withIdentifiant('participantInstant')
            ->withPrefNotifIdees(PrefNotifIdees::Instantanee)
            ->persist(self::$em);

        // Create an occasion
        $this->occasion()
            ->withParticipants([$utilisateur, $participantInstant])
            ->persist(self::$em);

        // Create an idea
        $idee = $this->idee()
            ->byAuteur($auteur)
            ->forUtilisateur($utilisateur)
            ->persist(self::$em);

        // Clear emails from creation
        $this->depileDerniersEmailsRecus();

        $this->postConnexion($curl, $auteur);

        // Mark the idea as deleted
        $this->requestApi(
            $curl,
            'POST',
            "/idee/{$idee->getId()}/suppression",
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);

        // Verify that a deletion email was sent
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($participantInstant->getEmail(), $emailsRecus[0]);
        $this->assertEquals("Idée de cadeau supprimée pour {$utilisateur->getNom()}", $emailsRecus[0]->subject);
    }

    /**
     * Test that no instant notification is sent if user preference is set to None
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testPasDeNotificationSiPreferenceAucune(bool $curl): void
    {
        $auteur = $this->utilisateur()->withIdentifiant('auteur')->persist(self::$em);
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Participant who doesn't want notifications
        $participantAucune = $this->utilisateur()
            ->withIdentifiant('participantAucune')
            ->withPrefNotifIdees(PrefNotifIdees::Aucune)
            ->persist(self::$em);

        // Create an occasion
        $this->occasion()
            ->withParticipants([$utilisateur, $participantAucune])
            ->persist(self::$em);

        $this->postConnexion($curl, $auteur);

        // Create an idea
        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $utilisateur->getId(),
                'description' => 'Test no notification',
                'idAuteur' => $auteur->getId(),
            ]
        );
        $this->assertEquals(200, $statusCode);

        // Verify that no email was sent
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(0, $emailsRecus);
    }

    /**
     * Test that daily digest notifications are sent correctly
     */
    public function testNotificationQuotidienneDigest(): void
    {
        $auteur = $this->utilisateur()->withIdentifiant('auteur')->persist(self::$em);
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Participant who wants daily notifications
        $participantQuotidien = $this->utilisateur()
            ->withIdentifiant('participantQuotidien')
            ->withPrefNotifIdees(PrefNotifIdees::Quotidienne)
            ->withDateDerniereNotifPeriodique(new DateTime('yesterday'))
            ->persist(self::$em);

        // Create an occasion
        $this->occasion()
            ->withParticipants([$utilisateur, $participantQuotidien])
            ->persist(self::$em);

        // Create multiple ideas
        $this->idee()
            ->byAuteur($auteur)
            ->forUtilisateur($utilisateur)
            ->withDescription('Première idée')
            ->persist(self::$em);
        $this->idee()
            ->byAuteur($auteur)
            ->forUtilisateur($utilisateur)
            ->withDescription('Deuxième idée')
            ->persist(self::$em);

        // Clear any instant notification emails
        $this->depileDerniersEmailsRecus();

        // Run the daily notification command
        exec("php bin/notif-quotidienne.php", $output, $return_var);
        $this->assertEquals(0, $return_var);
        $this->assertMatchesRegularExpression("/NotifCommande: {$participantQuotidien->getNom()} 2/", implode("\n", $output));

        // Verify that a daily digest email was sent
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($participantQuotidien->getEmail(), $emailsRecus[0]);
        $this->assertEquals('Actualités Tkdo', $emailsRecus[0]->subject);
        $this->assertMatchesRegularExpression("/nouvelles idées de cadeaux/", $emailsRecus[0]->body);
    }

    /**
     * Test that daily digest is not sent twice on the same day
     */
    public function testNotificationQuotidiennePasEnvoyeeDeuxFois(): void
    {
        $auteur = $this->utilisateur()->withIdentifiant('auteur')->persist(self::$em);
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Participant who wants daily notifications
        $participantQuotidien = $this->utilisateur()
            ->withIdentifiant('participantQuotidien')
            ->withPrefNotifIdees(PrefNotifIdees::Quotidienne)
            ->withDateDerniereNotifPeriodique(new DateTime('yesterday'))
            ->persist(self::$em);

        // Create an occasion
        $this->occasion()
            ->withParticipants([$utilisateur, $participantQuotidien])
            ->persist(self::$em);

        // Create an idea
        $this->idee()
            ->byAuteur($auteur)
            ->forUtilisateur($utilisateur)
            ->withDescription('Test idea')
            ->persist(self::$em);

        // Clear any instant notification emails
        $this->depileDerniersEmailsRecus();

        // Run the daily notification command first time
        exec("php bin/notif-quotidienne.php", $output, $return_var);
        $this->assertEquals(0, $return_var);

        // Verify that an email was sent
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);

        // Run the command again
        $output = [];
        exec("php bin/notif-quotidienne.php", $output, $return_var);
        $this->assertEquals(0, $return_var);

        // Verify that no email was sent the second time
        $this->assertDoesNotMatchRegularExpression("/NotifCommande: {$participantQuotidien->getNom()}/", implode("\n", $output));
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(0, $emailsRecus);
    }

    /**
     * Test that only participants in the same occasion receive notifications
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testNotificationSeulementParticipantsMemeOccasion(bool $curl): void
    {
        $auteur = $this->utilisateur()->withIdentifiant('auteur')->persist(self::$em);
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Participant in the same occasion
        $participantDansOccasion = $this->utilisateur()
            ->withIdentifiant('participantDansOccasion')
            ->withPrefNotifIdees(PrefNotifIdees::Instantanee)
            ->persist(self::$em);

        // Participant in a different occasion
        $participantAutreOccasion = $this->utilisateur()
            ->withIdentifiant('participantAutreOccasion')
            ->withPrefNotifIdees(PrefNotifIdees::Instantanee)
            ->persist(self::$em);

        // Create first occasion with utilisateur and participantDansOccasion
        $this->occasion()
            ->withParticipants([$utilisateur, $participantDansOccasion])
            ->persist(self::$em);

        // Create second occasion with only participantAutreOccasion
        $this->occasion()
            ->withTitre('Autre occasion')
            ->withParticipants([$participantAutreOccasion])
            ->persist(self::$em);

        $this->postConnexion($curl, $auteur);

        // Create an idea for utilisateur
        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $utilisateur->getId(),
                'description' => 'Test filtering',
                'idAuteur' => $auteur->getId(),
            ]
        );
        $this->assertEquals(200, $statusCode);

        // Verify that only participantDansOccasion received the notification
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($participantDansOccasion->getEmail(), $emailsRecus[0]);
    }

    /**
     * Test that the author of the idea does not receive a notification
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testAuteurNePasRecevoirNotification(bool $curl): void
    {
        $auteur = $this->utilisateur()
            ->withIdentifiant('auteur')
            ->withPrefNotifIdees(PrefNotifIdees::Instantanee)
            ->persist(self::$em);
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Create an occasion where the author is a participant
        $this->occasion()
            ->withParticipants([$utilisateur, $auteur])
            ->persist(self::$em);

        $this->postConnexion($curl, $auteur);

        // Create an idea (author creates it for themselves)
        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $utilisateur->getId(),
                'description' => 'Test author exclusion',
                'idAuteur' => $auteur->getId(),
            ]
        );
        $this->assertEquals(200, $statusCode);

        // Verify that the author did not receive a notification
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(0, $emailsRecus);
    }

    /**
     * Test daily digest includes both created and deleted ideas
     */
    public function testNotificationQuotidienneInclutCreationEtSuppression(): void
    {
        $auteur = $this->utilisateur()->withIdentifiant('auteur')->persist(self::$em);
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Participant who wants daily notifications
        $participantQuotidien = $this->utilisateur()
            ->withIdentifiant('participantQuotidien')
            ->withPrefNotifIdees(PrefNotifIdees::Quotidienne)
            ->withDateDerniereNotifPeriodique(new DateTime('yesterday'))
            ->persist(self::$em);

        // Create an occasion
        $this->occasion()
            ->withParticipants([$utilisateur, $participantQuotidien])
            ->persist(self::$em);

        // Create an idea
        $idee = $this->idee()
            ->byAuteur($auteur)
            ->forUtilisateur($utilisateur)
            ->withDescription('Idée à supprimer')
            ->persist(self::$em);

        // Mark it as deleted
        $idee->setDateSuppression(new DateTime());
        self::$em->flush();

        // Create another idea that's still active
        $this->idee()
            ->byAuteur($auteur)
            ->forUtilisateur($utilisateur)
            ->withDescription('Nouvelle idée')
            ->persist(self::$em);

        // Clear any instant notification emails
        $this->depileDerniersEmailsRecus();

        // Run the daily notification command
        exec("php bin/notif-quotidienne.php", $output, $return_var);
        $this->assertEquals(0, $return_var);

        // Verify that the digest includes both the deletion and creation
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($participantQuotidien->getEmail(), $emailsRecus[0]);
        $this->assertMatchesRegularExpression("/nouvelle idée de cadeau/", $emailsRecus[0]->body);
        $this->assertMatchesRegularExpression("/a été retirée de sa liste/", $emailsRecus[0]->body);
    }

    /**
     * Test that instant and daily notifications coexist correctly
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testNotificationInstantaneeEtQuotidienneCoexistent(bool $curl): void
    {
        $auteur = $this->utilisateur()->withIdentifiant('auteur')->persist(self::$em);
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // One participant wants instant notifications
        $participantInstant = $this->utilisateur()
            ->withIdentifiant('participantInstant')
            ->withPrefNotifIdees(PrefNotifIdees::Instantanee)
            ->persist(self::$em);

        // Another wants daily notifications
        $participantQuotidien = $this->utilisateur()
            ->withIdentifiant('participantQuotidien')
            ->withPrefNotifIdees(PrefNotifIdees::Quotidienne)
            ->withDateDerniereNotifPeriodique(new DateTime('yesterday'))
            ->persist(self::$em);

        // Create an occasion with both participants
        $this->occasion()
            ->withParticipants([$utilisateur, $participantInstant, $participantQuotidien])
            ->persist(self::$em);

        $this->postConnexion($curl, $auteur);

        // Create an idea
        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $utilisateur->getId(),
                'description' => 'Test coexistence',
                'idAuteur' => $auteur->getId(),
            ]
        );
        $this->assertEquals(200, $statusCode);

        // Verify that only the instant notification participant received an email
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($participantInstant->getEmail(), $emailsRecus[0]);

        // Run the daily notification command
        exec("php bin/notif-quotidienne.php", $output, $return_var);
        $this->assertEquals(0, $return_var);

        // Verify that the daily notification participant now receives their digest
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($participantQuotidien->getEmail(), $emailsRecus[0]);
    }

    /**
     * Test that daily digest is not sent if there are no ideas to report
     */
    public function testNotificationQuotidiennePasDIdees(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Participant who wants daily notifications but has no ideas
        $participantQuotidien = $this->utilisateur()
            ->withIdentifiant('participantQuotidien')
            ->withPrefNotifIdees(PrefNotifIdees::Quotidienne)
            ->withDateDerniereNotifPeriodique(new DateTime('yesterday'))
            ->persist(self::$em);

        // Create an occasion
        $this->occasion()
            ->withParticipants([$utilisateur, $participantQuotidien])
            ->persist(self::$em);

        // Run the daily notification command without creating any ideas
        exec("php bin/notif-quotidienne.php", $output, $return_var);
        $this->assertEquals(0, $return_var);

        // Verify that no email was sent (command still outputs the user with 0 ideas)
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(0, $emailsRecus);
    }

    /**
     * Test that daily digest only includes ideas from the current period
     */
    public function testNotificationQuotidienneSeulementIdeesPeriode(): void
    {
        $auteur = $this->utilisateur()->withIdentifiant('auteur')->persist(self::$em);
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Participant who wants daily notifications
        $participantQuotidien = $this->utilisateur()
            ->withIdentifiant('participantQuotidien')
            ->withPrefNotifIdees(PrefNotifIdees::Quotidienne)
            ->withDateDerniereNotifPeriodique(new DateTime('yesterday'))
            ->persist(self::$em);

        // Create an occasion
        $this->occasion()
            ->withParticipants([$utilisateur, $participantQuotidien])
            ->persist(self::$em);

        // Create an old idea (before the last notification)
        $this->idee()
            ->byAuteur($auteur)
            ->forUtilisateur($utilisateur)
            ->withDescription('Old idea')
            ->withDateProposition(new DateTime('2 days ago'))
            ->persist(self::$em);

        // Create a new idea (today)
        $this->idee()
            ->byAuteur($auteur)
            ->forUtilisateur($utilisateur)
            ->withDescription('New idea')
            ->persist(self::$em);

        // Clear any instant notification emails
        $this->depileDerniersEmailsRecus();

        // Run the daily notification command
        exec("php bin/notif-quotidienne.php", $output, $return_var);
        $this->assertEquals(0, $return_var);

        // Verify that only 1 idea is reported (the new one)
        $this->assertMatchesRegularExpression("/NotifCommande: {$participantQuotidien->getNom()} 1/", implode("\n", $output));

        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
    }
}
