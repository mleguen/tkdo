<?php

declare(strict_types=1);

namespace App\Appli\Service;

use App\Appli\Settings\MailSettings;
use Exception;

class MailService
{
    private $settings;

    public function __construct(MailSettings $settings)
    {
        $this->settings = $settings;
    }
    
    public function envoieMail(string $to, string $subject, string $message) : bool
    {
        try {
            $additional_headers = [
                'From' => $this->settings->from,
            ];

            return mail($to, $subject, $message, $additional_headers);
        }
        catch (Exception $e) {
            return false;
        }
    }
}
