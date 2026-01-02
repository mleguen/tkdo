<?php

declare(strict_types=1);

namespace Test\Int;

use App\Dom\Model\Utilisateur;

/**
 * Exclusion management integration test
 *
 * Tests the happy path for creating and listing exclusions.
 * Error cases (401, 403, 404, validation) are tested in ErrorHandlingIntTest.
 */
class ExclusionIntTest extends IntTestCase
{
    /**
     * Test exclusion management workflow
     *
     * Verifies: create exclusions → list exclusions
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testExclusionWorkflow(bool $curl): void
    {
        $admin = $this->utilisateur()->withIdentifiant('admin')->withAdmin()->persist(self::$em);
        $this->postConnexion($curl, $admin);

        // Verify new user has no exclusions
        $quiOffre = $this->utilisateur()->withIdentifiant('quiOffre')->persist(self::$em);
        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);
        $this->assertEquals([], $body);

        // Create first exclusion
        $quiNeDoitPasRecevoir1 = $this->utilisateur()->withIdentifiant('quiNeDoitPasRecevoir1')->persist(self::$em);
        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body,
            '',
            ['idQuiNeDoitPasRecevoir' => $quiNeDoitPasRecevoir1->getId()]
        );
        $this->assertEquals(200, $statusCode);
        $this->assertEquals(self::exclusionAttendue($quiNeDoitPasRecevoir1), $body);

        // Verify it appears in exclusion list
        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);
        $this->assertEquals([self::exclusionAttendue($quiNeDoitPasRecevoir1)], $body);

        // Create second exclusion
        $quiNeDoitPasRecevoir2 = $this->utilisateur()->withIdentifiant('quiNeDoitPasRecevoir2')->persist(self::$em);
        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body,
            '',
            ['idQuiNeDoitPasRecevoir' => $quiNeDoitPasRecevoir2->getId()]
        );
        $this->assertEquals(200, $statusCode);
        $this->assertEquals(self::exclusionAttendue($quiNeDoitPasRecevoir2), $body);

        // Verify both exclusions appear in list
        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);
        $this->assertEquals(array_map([self::class, 'exclusionAttendue'], [
            $quiNeDoitPasRecevoir1,
            $quiNeDoitPasRecevoir2
        ]), $body);
    }

    protected static function exclusionAttendue(Utilisateur $utilisateur): array
    {
        return [
            'quiNeDoitPasRecevoir' => self::utilisateurAttendu($utilisateur)
        ];
    }
}
