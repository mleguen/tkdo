<?php
declare(strict_types=1);

use App\Application\Actions\Connexion\CreateConnexionAction;
use App\Application\Actions\Occasion\ViewOccasionAction;
use App\Application\Actions\Utilisateur\ViewUtilisateurAction;
use App\Application\Actions\Idee\DeleteIdeeAction;
use App\Application\Actions\Idee\ListIdeeAction;
use App\Application\Actions\Idee\CreateIdeeAction;
use App\Application\Actions\Utilisateur\EditUtilisateurAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->post('/connexion', CreateConnexionAction::class);
    $app->group('/idee', function (Group $group) {
        $group->get('', ListIdeeAction::class);
        $group->post('', CreateIdeeAction::class);
        $group->delete('/{idIdee}', DeleteIdeeAction::class);
    });
    $app->get('/occasion', ViewOccasionAction::class);
    $app->group('/utilisateur/{idUtilisateur}', function (Group $group) {
        $group->get('', ViewUtilisateurAction::class);
        $group->put('', EditUtilisateurAction::class);
    });
};
