<?php

declare(strict_types=1);

namespace Test\Int;

use App\Dom\Model\PrefNotifIdees;
use DateTime;
use DateTimeInterface;

/**
 * Complete gift exchange workflow integration tests
 *
 * Tests the full user journey from occasion creation through draw generation
 * and result viewing. Focuses on happy paths and verifies all components
 * (database, email, API) work together correctly.
 */
class WorkflowGiftExchangeIntTest extends IntTestCase
{
    /**
     * Test complete gift exchange workflow: create occasion, add participants,
     * create ideas, generate draw, verify notifications and results.
     *
     * This is the primary happy path workflow for the gift exchange feature.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testCompleteGiftExchangeWorkflow(bool $curl): void
    {
        // Setup: Create admin and occasion
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $occasion = $this->creeOccasionEnMemoire();

        $this->postConnexion($curl, $admin);

        // Step 1: Admin creates occasion
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

        // Verify occasion appears in admin's list
        $this->requestApi($curl, 'GET', '/occasion', $statusCode, $body);
        $this->assertEquals(200, $statusCode);
        $this->assertEquals([self::occasionAttendue($occasion)], $body);

        // Step 2: Admin adds participants
        $participant1 = $this->creeUtilisateurEnBase('participant1');
        $participant2 = $this->creeUtilisateurEnBase('participant2');
        $participant3 = $this->creeUtilisateurEnBase('participant3', [
            'prefNotifIdees' => PrefNotifIdees::Instantanee
        ]);

        foreach ([$participant1, $participant2, $participant3] as $participant) {
            $this->requestApi(
                $curl,
                'POST',
                "/occasion/{$occasion->getId()}/participant",
                $statusCode,
                $body,
                '',
                ['idParticipant' => $participant->getId()]
            );
            $this->assertEquals(200, $statusCode);
            $occasion->addParticipant($participant);

            // Verify participant received participation email
            $emailsRecus = $this->depileDerniersEmailsRecus();
            $this->assertCount(1, $emailsRecus);
            $this->assertMessageRecipientsContains($participant->getEmail(), $emailsRecus[0]);
            $this->assertEquals("Participation au tirage cadeaux {$occasion->getTitre()}", $emailsRecus[0]->subject);
        }

        // Step 3: Participant creates idea for another participant
        $this->postConnexion($curl, $participant1);
        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $participant2->getId(),
                'description' => 'Un livre de science-fiction',
                'idAuteur' => $participant1->getId(),
            ]
        );
        $this->assertEquals(200, $statusCode);
        $this->assertArrayHasKey('id', $body);

        // Verify instant notification sent to participant3 (who wants instant notifications)
        // participant2 is the idea recipient, so doesn't get notified
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($participant3->getEmail(), $emailsRecus[0]);

        // Step 4: Admin generates draw
        $this->postConnexion($curl, $admin);
        $this->requestApi(
            $curl,
            'POST',
            "/occasion/{$occasion->getId()}/tirage",
            $statusCode,
            $body,
            '',
            []
        );
        $this->assertEquals(200, $statusCode);
        $this->assertArrayHasKey('resultats', $body);
        $this->assertCount(3, $body['resultats']);
        // Draw validity (everyone gives/receives once, no self-gifting) is tested in unit tests

        // Verify all participants received draw notification
        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(3, $emailsRecus);

        // Step 5: Each participant views their own result only
        foreach ([$participant1, $participant2, $participant3] as $participant) {
            $this->postConnexion($curl, $participant);
            $this->requestApi(
                $curl,
                'GET',
                "/occasion/{$occasion->getId()}",
                $statusCode,
                $body
            );
            $this->assertEquals(200, $statusCode);
            $this->assertArrayHasKey('resultats', $body);
            $this->assertCount(1, $body['resultats']); // Only sees their own result
            $this->assertEquals($participant->getId(), $body['resultats'][0]['idQuiOffre']);
        }
    }

}
