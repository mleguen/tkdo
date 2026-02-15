<?php

declare(strict_types=1);

namespace App\Appli\Settings;

class UriSettings
{
    public string $baseUri;

    public function __construct()
    {
        $this->baseUri = getenv('TKDO_API_BASE_URI') ?: 'http://localhost:4200';
    }
}
