<?php

declare(strict_types=1);

namespace Test\Int;

class ConnexionIntTest extends IntTestCase
{
    /** @dataProvider provideCurl */
    public function testCasNominal(bool $curl): void
    {
        $this->postConnexion($curl);
    }

    /** @dataProvider provideCurl */
    public function testMauvaisMdp(bool $curl): void
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        $this->requestApi(
            $curl,
            'POST',
            '/api/connexion',
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

    /** @dataProvider provideCurl */
    public function testIdentifiantInconnu(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'POST',
            '/api/connexion',
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
