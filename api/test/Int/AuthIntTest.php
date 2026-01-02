<?php

declare(strict_types=1);

namespace Test\Int;

/**
 * Cas aux limites sur l'authentification non couverts par les autres tests E2E
 */
class AuthIntTest extends IntTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    function testGetUtilisateurTokenInvalide(bool $curl)
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);
        $this->token = 'invalide';

        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/{$utilisateur->getId()}",
            $statusCode,
            $body
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification invalide",
        ], $body);
    }
}
