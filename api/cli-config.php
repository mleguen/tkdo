<?php

use App\Bootstrap;

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Bootstrap();
$bootstrap->initEnv();
$bootstrap->initContainer();
return $bootstrap->initDoctrineMigration();
