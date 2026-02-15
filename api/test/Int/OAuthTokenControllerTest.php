<?php

declare(strict_types=1);

namespace Test\Int;

use App\Appli\ModelAdaptor\AuthCodeAdaptor;
use DateTime;

class OAuthTokenControllerTest extends IntTestCase
{
    private const TOKEN_PATH = '/oauth/token';
    private const AUTHORIZE_PATH = '/oauth/authorize';

    /**
     * Helper: create a user and get an auth code via the authorize endpoint.
     *
     * @return array{code: string, utilisateur: \App\Appli\ModelAdaptor\UtilisateurAdaptor}
     */
    private function createUserAndGetCode(): array
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        $baseUri = getenv('TKDO_BASE_URI');
        $client = new \GuzzleHttp\Client(['allow_redirects' => false]);

        $response = $client->request(
            'POST',
            $baseUri . self::AUTHORIZE_PATH,
            [
                'form_params' => [
                    'identifiant' => $utilisateur->getIdentifiant(),
                    'mdp' => $utilisateur->getMdpClair(),
                    'client_id' => 'tkdo',
                    'redirect_uri' => 'http://localhost:4200/auth/callback',
                    'response_type' => 'code',
                    'state' => 'test',
                ],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(302, $response->getStatusCode());
        $location = $response->getHeaderLine('Location');
        parse_str(parse_url($location, PHP_URL_QUERY) ?: '', $queryParams);

        return ['code' => $queryParams['code'], 'utilisateur' => $utilisateur];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testValidCodeReturnsStandardTokenResponse(bool $curl): void
    {
        ['code' => $code] = $this->createUserAndGetCode();

        $this->requestApi(
            $curl,
            'POST',
            self::TOKEN_PATH,
            $statusCode,
            $body,
            '',
            [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => 'tkdo',
                'client_secret' => 'dev-secret',
            ]
        );

        $this->assertEquals(200, $statusCode);
        $this->assertNotNull($body);
        $this->assertArrayHasKey('access_token', $body);
        $this->assertArrayHasKey('token_type', $body);
        $this->assertArrayHasKey('expires_in', $body);
        $this->assertEquals('Bearer', $body['token_type']);
        $this->assertEquals(3600, $body['expires_in']);

        // access_token should be a JWT (3 dot-separated segments)
        $this->assertCount(3, explode('.', $body['access_token']));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testExpiredCodeReturns401(bool $curl): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Create an expired auth code directly in DB
        $authCode = new AuthCodeAdaptor();
        $authCode->setCodeHash('expired_test_code');
        $authCode->setUtilisateurId($utilisateur->getId());
        $authCode->setExpiresAt(new DateTime('-1 second'));
        self::$em->persist($authCode);
        self::$em->flush();

        $this->requestApi(
            $curl,
            'POST',
            self::TOKEN_PATH,
            $statusCode,
            $body,
            '',
            [
                'grant_type' => 'authorization_code',
                'code' => 'expired_test_code',
                'client_id' => 'tkdo',
                'client_secret' => 'dev-secret',
            ]
        );

        $this->assertEquals(401, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testAlreadyUsedCodeReturns401(bool $curl): void
    {
        ['code' => $code] = $this->createUserAndGetCode();

        // First exchange should succeed
        $this->requestApi(
            $curl,
            'POST',
            self::TOKEN_PATH,
            $firstStatusCode,
            $firstBody,
            '',
            [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => 'tkdo',
                'client_secret' => 'dev-secret',
            ]
        );
        $this->assertEquals(200, $firstStatusCode);

        // Second exchange with same code should fail
        $this->requestApi(
            $curl,
            'POST',
            self::TOKEN_PATH,
            $secondStatusCode,
            $secondBody,
            '',
            [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => 'tkdo',
                'client_secret' => 'dev-secret',
            ]
        );

        $this->assertEquals(401, $secondStatusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testInvalidCodeReturns401(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'POST',
            self::TOKEN_PATH,
            $statusCode,
            $body,
            '',
            [
                'grant_type' => 'authorization_code',
                'code' => 'totally_invalid_code',
                'client_id' => 'tkdo',
                'client_secret' => 'dev-secret',
            ]
        );

        $this->assertEquals(401, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testMissingGrantTypeReturns400(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'POST',
            self::TOKEN_PATH,
            $statusCode,
            $body,
            '',
            [
                'code' => 'some_code',
                'client_id' => 'tkdo',
                'client_secret' => 'dev-secret',
            ]
        );

        $this->assertEquals(400, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testInvalidGrantTypeReturns400(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'POST',
            self::TOKEN_PATH,
            $statusCode,
            $body,
            '',
            [
                'grant_type' => 'password',
                'code' => 'some_code',
                'client_id' => 'tkdo',
                'client_secret' => 'dev-secret',
            ]
        );

        $this->assertEquals(400, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testInvalidClientSecretReturns401(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'POST',
            self::TOKEN_PATH,
            $statusCode,
            $body,
            '',
            [
                'grant_type' => 'authorization_code',
                'code' => 'some_code',
                'client_id' => 'tkdo',
                'client_secret' => 'wrong-secret',
            ]
        );

        $this->assertEquals(401, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testMissingClientSecretReturns401(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'POST',
            self::TOKEN_PATH,
            $statusCode,
            $body,
            '',
            [
                'grant_type' => 'authorization_code',
                'code' => 'some_code',
                'client_id' => 'tkdo',
            ]
        );

        $this->assertEquals(401, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testInvalidClientIdReturns401(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'POST',
            self::TOKEN_PATH,
            $statusCode,
            $body,
            '',
            [
                'grant_type' => 'authorization_code',
                'code' => 'some_code',
                'client_id' => 'wrong-client',
                'client_secret' => 'dev-secret',
            ]
        );

        $this->assertEquals(401, $statusCode);
    }

    /**
     * Test concurrent code exchange — exactly one should succeed.
     */
    public function testConcurrentCodeExchangeOnlyOneSucceeds(): void
    {
        ['code' => $code] = $this->createUserAndGetCode();

        $baseUri = getenv('TKDO_BASE_URI');
        $concurrency = 5;
        $handles = [];
        $multiHandle = curl_multi_init();

        for ($i = 0; $i < $concurrency; $i++) {
            $ch = curl_init($baseUri . self::TOKEN_PATH);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => 'tkdo',
                'client_secret' => 'dev-secret',
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $handles[] = $ch;
            curl_multi_add_handle($multiHandle, $ch);
        }

        do {
            $status = curl_multi_exec($multiHandle, $active);
            if ($active) {
                curl_multi_select($multiHandle);
            }
        } while ($active && $status === CURLM_OK);

        $statusCodes = [];
        foreach ($handles as $ch) {
            $statusCodes[] = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }
        curl_multi_close($multiHandle);

        $successes = array_filter($statusCodes, fn(int $code) => $code === 200);
        $failures = array_filter($statusCodes, fn(int $code) => $code === 401);

        $this->assertCount(1, $successes, 'Exactly one concurrent request should succeed');
        $this->assertCount($concurrency - 1, $failures, 'All other concurrent requests should get 401');
    }
}
