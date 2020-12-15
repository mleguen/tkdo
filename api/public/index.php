<?php
declare(strict_types=1);

use App\Bootstrap;
use App\Appli\Handler\ShutdownHandler;
use App\Appli\Settings\ErrorSettings;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\ResponseEmitter;

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Bootstrap();
$bootstrap->initEnv();
$container = $bootstrap->initContainer();
$slimApp = $bootstrap->initSlimApp();
$errorHandler = $bootstrap->initSlimErrorHandling();

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Shutdown Handler
$errorSettings = $container->get(ErrorSettings::class);
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $errorSettings);
register_shutdown_function($shutdownHandler);

// Run App & Emit Response
$response = $slimApp->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
