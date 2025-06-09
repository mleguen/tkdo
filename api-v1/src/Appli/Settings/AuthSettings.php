<?php

declare(strict_types=1);

namespace App\Appli\Settings;

use App\Bootstrap;

class AuthSettings
{
    public $algo = 'RS256';
    public $fichierClePrivee;
    public $fichierClePublique;
    public $validite = 3600;

    public function __construct(
        Bootstrap $bootstrap
    )
    {
        $this->fichierClePrivee = "$bootstrap->apiRoot/var/auth/auth_rsa";
        $this->fichierClePublique = "$bootstrap->apiRoot/var/auth/auth_rsa.pub";
    }
}
