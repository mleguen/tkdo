<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/4.x/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace App\Appli\Handler;

use App\Appli\Settings\LogSettings;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;

class AppLogger extends Logger
{
    public function __construct(
        LogSettings $settings
    )
    {
        parent::__construct($settings->name);
        $this->pushProcessor(new UidProcessor());
        $this->pushHandler(new StreamHandler($settings->stream, $settings->level));
    }
}
