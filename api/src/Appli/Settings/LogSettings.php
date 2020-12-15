<?php

declare(strict_types=1);

namespace App\Appli\Settings;

use App\Bootstrap;
use Monolog\Logger;

class LogSettings
{
    public $level = Logger::DEBUG;
    public $name = 'api';
    public $stream;

    public function __construct(
        Bootstrap $bootstrap
    )
    {
        $this->stream = $bootstrap->docker ? 'php://stdout' : "$bootstrap->apiRoot/var/log/$this->name.log";
    }
}
