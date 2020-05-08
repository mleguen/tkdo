<?php
declare(strict_types=1);

use App\Application\Actions\Connexion\ConnexionAction;
use App\Application\Actions\Occasion\OccasionReadAction;
use App\Application\Actions\Utilisateur\UtilisateurReadAction;
use App\Application\Actions\Idee\IdeeDeleteAction;
use App\Application\Actions\Idee\IdeeReadAllAction;
use App\Application\Actions\Idee\IdeeCreateAction;
use App\Application\Actions\Utilisateur\UtilisateurUpdateAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->post('/connexion', ConnexionAction::class);
    $app->get('/occasion', OccasionReadAction::class);
    $app->group('/utilisateur/{idUtilisateur}', function (Group $group) {
        $group->get('', UtilisateurReadAction::class);
        $group->put('', UtilisateurUpdateAction::class);
        $group->group('/idee', function (Group $group) {
            $group->get('', IdeeReadAllAction::class);
            $group->post('', IdeeCreateAction::class);
            $group->delete('/{idIdee}', IdeeDeleteAction::class);
        });
    });
};
