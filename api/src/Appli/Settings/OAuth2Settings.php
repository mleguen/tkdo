<?php

// PERMANENT: Stays when switching to external IdP

declare(strict_types=1);

namespace App\Appli\Settings;

class OAuth2Settings
{
    public string $clientId;
    public string $clientSecret;
    public string $redirectUri;
    public string $urlAuthorize;
    public string $urlAccessToken;
    public string $urlResourceOwner;

    public function __construct()
    {
        $this->clientId = (string) (getenv('OAUTH2_CLIENT_ID') ?: 'tkdo');
        $this->clientSecret = (string) (getenv('OAUTH2_CLIENT_SECRET') ?: 'dev-secret');
        $baseUri = (string) (getenv('TKDO_BASE_URI') ?: 'http://localhost:4200');
        $this->redirectUri = $baseUri . '/auth/callback';

        // TEMPORARY: Points to the built-in auth server; will be replaced by external IdP URLs
        $issuerBaseUri = getenv('OAUTH2_ISSUER_BASE_URI')
            ?: $baseUri;
        $this->urlAuthorize = $issuerBaseUri . '/oauth/authorize';
        $this->urlAccessToken = $issuerBaseUri . '/oauth/token';
        $this->urlResourceOwner = $issuerBaseUri . '/oauth/userinfo';
    }
}
