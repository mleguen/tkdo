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
                'Content-Type' => 'text/plain; charset=UTF-8',
            ];

            return mail($to, mb_encode_mimeheader($subject, 'UTF-8'), $message, $additional_headers);
        }
        catch (Exception) {
            return false;
        }
    }
}
