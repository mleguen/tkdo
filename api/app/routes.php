<?php
declare(strict_types=1);

use App\Application\Actions\Connexion\PostConnexionAction;
use App\Application\Actions\Occasion\GetOccasionAction;
use App\Application\Actions\Utilisateur\GetUtilisateurAction;
use App\Application\Actions\Utilisateur\Idee\DeleteUtilisateurIdeeAction;
use App\Application\Actions\Utilisateur\Idee\GetUtilisateurIdeesAction;
use App\Application\Actions\Utilisateur\Idee\PostUtilisateurIdeeAction;
use App\Application\Actions\Utilisateur\PutUtilisateurAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->post('/connexion', PostConnexionAction::class);
    $app->get('/occasion', GetOccasionAction::class);
    $app->group('/utilisateur/{idUtilisateur}', function (Group $group) {
        $group->get('', GetUtilisateurAction::class);
        $group->put('', PutUtilisateurAction::class);
        $group->group('/idee', function (Group $group) {
            $group->get('', GetUtilisateurIdeesAction::class);
            $group->post('', PostUtilisateurIdeeAction::class);
            $group->delete('/{idIdee}', DeleteUtilisateurIdeeAction::class);
        });
    });
};
