<?php
declare(strict_types=1);

use App\Application\Middleware\AuthMiddleware;
use Slim\App;

return function (App $app) {
    // TODO: à remplacer par Tuupola\Middleware\JwtAuthentication
    $app->add(AuthMiddleware::class);
};
