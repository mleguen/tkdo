<?php

// PERMANENT: Stays when switching to external IdP

declare(strict_types=1);

namespace App\Appli\Service;

use App\Appli\Settings\OAuth2Settings;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * PERMANENT: BFF OAuth2 client service.
 * Wraps league/oauth2-client GenericProvider for authorization code exchange.
 * No temp-auth-server-specific logic — works with any OAuth2 provider.
 */
class BffAuthService
{
    private GenericProvider $provider;

    public function __construct(OAuth2Settings $settings)
    {
        $this->provider = new GenericProvider([
            'clientId' => $settings->clientId,
            'clientSecret' => $settings->clientSecret,
            'redirectUri' => $settings->redirectUri,
            'urlAuthorize' => $settings->urlAuthorize,
            'urlAccessToken' => $settings->urlAccessToken,
            'urlResourceOwnerDetails' => $settings->urlResourceOwner,
        ]);
    }

    /**
     * Exchange an authorization code for an access token via back-channel call.
     */
    public function echangeCode(string $code): AccessTokenInterface
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
    }

    /**
     * Extract user claims from the access token JWT.
     *
     * @return array{sub: int, adm: bool, groupe_ids: int[]}
     */
    public function extraitInfoUtilisateur(AccessTokenInterface $token): array
    {
        $jwt = $token->getToken();

        // Decode JWT payload (second segment, base64url-encoded)
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new \RuntimeException('access_token JWT invalide');
        }

        /** @var array<string, mixed>|null $payload */
        $payload = json_decode(
            base64_decode(strtr($parts[1], '-_', '+/')),
            true
        );

        if ($payload === null) {
            throw new \RuntimeException('payload JWT invalide');
        }

        /** @var int[] $groupeIds */
        $groupeIds = isset($payload['groupe_ids']) ? (array) $payload['groupe_ids'] : [];

        return [
            'sub' => (int) ($payload['sub'] ?? 0),
            'adm' => isset($payload['adm']) && $payload['adm'],
            'groupe_ids' => $groupeIds,
        ];
    }
}
