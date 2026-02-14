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
        $this->redirectUri = (string) (getenv('OAUTH2_REDIRECT_URI') ?: 'http://localhost:4200/auth/callback');

        // TEMPORARY: These point to the built-in auth server; will change to external IdP URLs
        $basePath = getenv('TKDO_API_BASE_PATH') ?: '';
        $baseUri = getenv('TKDO_BASE_URI')
            ?: ('http://localhost:8080' . $basePath);
        $this->urlAuthorize = $baseUri . '/oauth/authorize';
        $this->urlAccessToken = $baseUri . '/oauth/token';
        $this->urlResourceOwner = $baseUri . '/oauth/userinfo'; // Not used (claims in token)
    }
}
