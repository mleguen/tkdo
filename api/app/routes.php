<?php

declare(strict_types=1);

use App\Application\Actions\Connexion\ListConnexionAction;
use App\Application\Actions\Utilisateur\ListUtilisateurAction;
use App\Application\Actions\Occasion\ListOccasionAction;
use App\Application\Actions\Idee\ListIdeeAction;
use App\Application\Actions\Resultat\ListResultatAction;
use App\Application\Actions\Connexion\ViewConnexionAction;
use App\Application\Actions\Utilisateur\ViewUtilisateurAction;
use App\Application\Actions\Occasion\ViewOccasionAction;
use App\Application\Actions\Idee\ViewIdeeAction;
use App\Application\Actions\Resultat\ViewResultatAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', fn(Request $request, Response $response) =>
        // CORS Pre-Flight OPTIONS Request Handler
        $response);

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/connexion', function (Group $group) {
        $group->get('', ListConnexionAction::class);
        $group->get('/{id}', ViewConnexionAction::class);
    });
    $app->group('/utilisateur', function (Group $group) {
        $group->get('', ListUtilisateurAction::class);
        $group->get('/{id}', ViewUtilisateurAction::class);
    });
    $app->group('/occasion', function (Group $group) {
        $group->get('', ListOccasionAction::class);
        $group->get('/{id}', ViewOccasionAction::class);
    });
    $app->group('/idee', function (Group $group) {
        $group->get('', ListIdeeAction::class);
        $group->get('/{id}', ViewIdeeAction::class);
    });
    $app->group('/resultat', function (Group $group) {
        $group->get('', ListResultatAction::class);
        $group->get('/{id}', ViewResultatAction::class);
    });
};
