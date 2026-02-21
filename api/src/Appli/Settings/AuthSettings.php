<?php

declare(strict_types=1);

namespace App\Appli\Settings;

use App\Bootstrap;

class AuthSettings
{
    public string $algo = 'RS256';
    public string $fichierClePrivee;
    public string $fichierClePublique;
    public int $validite = 3600;
    public int $validiteSeSouvenir = 604800;

    public function __construct(
        Bootstrap $bootstrap
    )
    {
        $this->fichierClePrivee = "$bootstrap->apiRoot/var/auth/auth_rsa";
        $this->fichierClePublique = "$bootstrap->apiRoot/var/auth/auth_rsa.pub";
    }
}
