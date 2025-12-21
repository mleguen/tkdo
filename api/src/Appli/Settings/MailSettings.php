<?php

declare(strict_types=1);

namespace App\Appli\Settings;

use App\Appli\Service\UriService;

class MailSettings
{
    public string $from;
    public string $signature = <<<EOS
Cordialement,
Votre administrateur Tkdo.
EOS;

    public function __construct(UriService $uriService)
    {
        $this->from = getenv('TKDO_MAILER_FROM') ?: "Tkdo <noreply@{$uriService->getHost()}>";
    }
}
