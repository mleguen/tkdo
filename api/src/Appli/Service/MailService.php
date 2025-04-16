<?php

declare(strict_types=1);

namespace App\Appli\Service;

use App\Appli\Settings\MailSettings;
use Exception;

class MailService
{
    public function __construct(private readonly MailSettings $settings)
    {
    }
    
    public function envoieMail(string $to, string $subject, string $message) : bool
    {
        try {
            $additional_headers = [
                'From' => $this->settings->from,
            ];

            return mail($to, $subject, $message, $additional_headers);
        }
        catch (Exception) {
            return false;
        }
    }
}
