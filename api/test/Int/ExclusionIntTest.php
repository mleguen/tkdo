<?php

declare(strict_types=1);

namespace Test\Int;

use App\Dom\Model\Utilisateur;

class ExclusionIntTest extends IntTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testCasNominal(bool $curl): void
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $this->postConnexion($curl, $admin);

        // Vérifie qu'un utilisateur nouvellement créé n'a pas d'exclusion
        $quiOffre = $this->creeUtilisateurEnBase('quiOffre');
        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);
        $this->assertEquals([], $body);

        // Ajoute une 1ère exclusion
        $quiNeDoitPasRecevoir1 = $this->creeUtilisateurEnBase('quiNeDoitPasRecevoir1');
        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body,
            '',
            [
                'idQuiNeDoitPasRecevoir' => $quiNeDoitPasRecevoir1->getId(),
            ]
        );
        $this->assertEquals(self::exclusionAttendue($quiNeDoitPasRecevoir1), $body);
        $this->assertEquals(200, $statusCode);

        // Vérifie qu'elle apparaît dans la liste des exclusions
        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body
        );
        $this->assertEquals(200, $statusCode);
        $this->assertEquals(array_map([self::class, 'exclusionAttendue'], [
            $quiNeDoitPasRecevoir1
        ]), $body);

        // Ajoute une 2ème exclusion
        $quiNeDoitPasRecevoir2 = $this->creeUtilisateurEnBase('quiNeDoitPasRecevoir2');
        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body,
            '',
            [
                'idQuiNeDoitPasRecevoir' => $quiNeDoitPasRecevoir2->getId(),
            ]
        );
        $this->assertEquals(200, $statusCode);
        $this->assertEquals(self::exclusionAttendue($quiNeDoitPasRecevoir2), $body);

        // Vérifie qu'elle apparaît aussi dans la liste des exclusions
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    function testCreeExceptionNonAuthentifie(bool $curl)
    {
        $quiOffre = $this->creeUtilisateurEnBase('quiOffre');
        $quiNeDoitPasRecevoir = $this->creeUtilisateurEnBase('quiNeDoitPasRecevoir');

        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body,
            '',
            [
                'idQuiNeDoitPasRecevoir' => $quiNeDoitPasRecevoir->getId(),
            ]
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    function testCreeExceptionPasAdmin(bool $curl)
    {
        $this->postConnexion($curl);
        $quiOffre = $this->creeUtilisateurEnBase('quiOffre');
        $quiNeDoitPasRecevoir = $this->creeUtilisateurEnBase('quiNeDoitPasRecevoir');

        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body,
            '',
            [
                'idQuiNeDoitPasRecevoir' => $quiNeDoitPasRecevoir->getId(),
            ]
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est pas un administrateur",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    function testCreeExceptionDoublon(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $this->postConnexion($curl, $admin);
       
        // Ajoute 2 fois la même exclusion
        $quiOffre = $this->creeUtilisateurEnBase('quiOffre');
        $quiNeDoitPasRecevoir1 = $this->creeUtilisateurEnBase('quiNeDoitPasRecevoir1');
        
        for ($i=0; $i<2; $i++) {
            $this->requestApi(
                $curl,
                'POST',
                "/utilisateur/{$quiOffre->getId()}/exclusion",
                $statusCode,
                $body,
                '',
                [
                    'idQuiNeDoitPasRecevoir' => $quiNeDoitPasRecevoir1->getId(),
                ]
            );
        }
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => "l'exclusion existe déjà",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    function testCreeExceptionQuiOffreInconnu(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $this->postConnexion($curl, $admin);
        
        $quiNeDoitPasRecevoir = $this->creeUtilisateurEnBase('quiNeDoitPasRecevoir');
        $idQuiOffre = $quiNeDoitPasRecevoir->getId() + 1;
        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/$idQuiOffre/exclusion",
            $statusCode,
            $body,
            '',
            [
                'idQuiNeDoitPasRecevoir' => $quiNeDoitPasRecevoir->getId(),
            ]
        );
        $this->assertEquals(404, $statusCode);
        $this->assertEquals([
            'message' => 'utilisateur inconnu',
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    function testCreeExceptionQuiNeDoitPasRecevoirInconnu(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $this->postConnexion($curl, $admin);
        
        $quiOffre = $this->creeUtilisateurEnBase('quiOffre');
        $idQuiNeDoitPasRecevoir = $quiOffre->getId() + 1;
        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body,
            '',
            [
                'idQuiNeDoitPasRecevoir' => $idQuiNeDoitPasRecevoir,
            ]
        );
        $this->assertEquals(400, $statusCode);
        $this->assertEquals([
            'message' => 'utilisateur inconnu',
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    function testListeExceptionNonAuthentifie(bool $curl)
    {
        $quiOffre = $this->creeUtilisateurEnBase('quiOffre');

        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body
        );
        $this->assertEquals(401, $statusCode);
        $this->assertEquals([
            'message' => "token d'authentification absent",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    function testListeExceptionPasAdmin(bool $curl)
    {
        $this->postConnexion($curl);
        $quiOffre = $this->creeUtilisateurEnBase('quiOffre');

        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/{$quiOffre->getId()}/exclusion",
            $statusCode,
            $body
        );
        $this->assertEquals(403, $statusCode);
        $this->assertEquals([
            'message' => "l'utilisateur authentifié n'est pas un administrateur",
        ], $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    function testListeExceptionQuiOffreInconnu(bool $curl)
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $this->postConnexion($curl, $admin);       
        $idQuiOffre = $admin->getId() + 1;

        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/$idQuiOffre/exclusion",
            $statusCode,
            $body
        );
        $this->assertEquals(404, $statusCode);
        $this->assertEquals([
            'message' => 'utilisateur inconnu',
        ], $body);
    }

    protected static function exclusionAttendue(Utilisateur $utilisateur): array
    {
        return [
            'quiNeDoitPasRecevoir' => self::utilisateurAttendu($utilisateur)
        ];
    }
}
