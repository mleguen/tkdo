<?php

// PERMANENT: Stays when switching to external IdP

declare(strict_types=1);

namespace App\Appli\Service;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * PERMANENT: BFF OAuth2 client service.
 * Wraps league/oauth2-client GenericProvider for authorization code exchange.
 * No temp-auth-server-specific logic — works with any OAuth2 provider.
 */
class BffAuthService
{
    public function __construct(
        private readonly GenericProvider $provider
    ) {
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
     * Extract user identity from the access token via the resource owner endpoint.
     * Only extracts 'sub' (user ID) — all application-specific data (nom, email,
     * genre, admin, groups) is loaded from the database by BffAuthCallbackController.
     *
     * @return array{sub: int}
     */
    public function extraitInfoUtilisateur(AccessTokenInterface $token): array
    {
        if (!$token instanceof AccessToken) {
            throw new \RuntimeException('type de token inattendu');
        }

        $owner = $this->provider->getResourceOwner($token);
        $data = $owner->toArray();

        return [
            'sub' => (int) ($data['sub'] ?? 0),
        ];
    }
}
