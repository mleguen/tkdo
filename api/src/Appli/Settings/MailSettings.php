<?php

declare(strict_types=1);

namespace App\Appli\Settings;

class MailSettings
{
    public $from;
    public $signature = <<<EOS
Cordialement,
Votre administrateur Tkdo.
EOS;

    public function __construct()
    {
        $this->from = getenv('TKDO_MAILER_FROM');
    }
}
