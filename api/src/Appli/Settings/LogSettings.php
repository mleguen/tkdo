<?php

declare(strict_types=1);

namespace App\Appli\Settings;

use App\Bootstrap;
use Monolog\Logger;

class LogSettings
{
    /** @var 100|200|250|300|400|500|550|600 */
    public int $level = Logger::DEBUG;
    public string $name = 'api';
    public string $stream;

    public function __construct(
        Bootstrap $bootstrap
    )
    {
        $this->stream = $bootstrap->docker ? 'php://stdout' : "$bootstrap->apiRoot/var/log/$this->name.log";
    }
}
