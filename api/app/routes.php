<?php
declare(strict_types=1);

use App\Application\Actions\Connexion\CreateConnexionAction;
use App\Application\Actions\Occasion\ViewOccasionAction;
use App\Application\Actions\Utilisateur\ViewUtilisateurAction;
use App\Application\Actions\Idee\DeleteIdeeAction;
use App\Application\Actions\Idee\ListIdeeAction;
use App\Application\Actions\Idee\CreateIdeeAction;
use App\Application\Actions\Utilisateur\CreateUtilisateurAction;
use App\Application\Actions\Utilisateur\CreateUtilisateurReinitMdpAction;
use App\Application\Actions\Utilisateur\EditUtilisateurAction;
use App\Application\Actions\Utilisateur\ListUtilisateurAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->post('/connexion', CreateConnexionAction::class);
    $app->group('/idee', function (Group $group) {
        $group->get('', ListIdeeAction::class);
        $group->post('', CreateIdeeAction::class);
        $group->delete('/{idIdee}', DeleteIdeeAction::class);
    });
    $app->get('/occasion', ViewOccasionAction::class);
    $app->group('/utilisateur', function (Group $group) {
        $group->get('', ListUtilisateurAction::class);
        $group->post('', CreateUtilisateurAction::class);
        $group->group('/{idUtilisateur}', function (Group $group) {
            $group->get('', ViewUtilisateurAction::class);
            $group->put('', EditUtilisateurAction::class);
            $group->post('/reinitmdp', CreateUtilisateurReinitMdpAction::class);
        });
    });
};
