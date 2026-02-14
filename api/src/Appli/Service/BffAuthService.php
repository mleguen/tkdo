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
     * Extract user claims from the access token via the resource owner endpoint.
     * Uses standard GenericProvider::getResourceOwner() — no manual JWT decoding.
     *
     * @return array{sub: int, adm: bool, groupe_ids: int[]}
     */
    public function extraitInfoUtilisateur(AccessTokenInterface $token): array
    {
        if (!$token instanceof AccessToken) {
            throw new \RuntimeException('type de token inattendu');
        }

        $owner = $this->provider->getResourceOwner($token);
        $data = $owner->toArray();

        /** @var int[] $groupeIds */
        $groupeIds = isset($data['groupe_ids']) ? (array) $data['groupe_ids'] : [];

        return [
            'sub' => (int) ($data['sub'] ?? 0),
            'adm' => !empty($data['admin']),
            'groupe_ids' => $groupeIds,
        ];
    }
}
