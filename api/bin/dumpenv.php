<?php

declare(strict_types=1);

use App\Bootstrap;

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Bootstrap();
$bootstrap->initEnv();
var_dump(getenv());
