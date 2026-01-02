<?php

declare(strict_types=1);

namespace Test\Int;

class ConnexionIntTest extends IntTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testCasNominal(bool $curl): void
    {
        $this->postConnexion($curl);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testMauvaisMdp(bool $curl): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        $this->requestApi(
            $curl,
            'POST',
            '/connexion',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'mdp' => 'mauvais' . $utilisateur->getMdpClair(),
            ]
        );
        $this->assertEquals(400, $statusCode);
        
        $this->assertEquals([
            'message' => 'identifiants invalides',
        ], $body ?: []);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testIdentifiantInconnu(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'POST',
            '/connexion',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => 'mauvais',
                'mdp' => 'peuimporte',
            ]
        );
        $this->assertEquals(400, $statusCode);
        
        $this->assertEquals([
            'message' => 'identifiants invalides',
        ], $body ?: []);
    }
}
