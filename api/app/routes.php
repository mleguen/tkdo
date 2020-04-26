<?php
declare(strict_types=1);

use App\Application\Actions\Connexion\PostConnexionAction;
use App\Application\Actions\ListeIdees\GetListeIdeesAction;
use App\Application\Actions\Occasion\GetOccasionAction;
use App\Application\Actions\Profil\GetProfilAction;
use App\Application\Actions\Profil\PutProfilAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->post('/connexion', PostConnexionAction::class);
    // TODO: renommer en /utilisateur/{idUtilisateur}/liste-idees
    $app->group('/liste-idees/{idUtilisateur}', function (Group $groupListeIdees) {
        $groupListeIdees->get('', GetListeIdeesAction::class);
    //     $groupListeIdees->put('', PutProfilAction::class);
    //     $groupListeIdees->group('/idee/{idIdee}', function (Group $groupIdee) {
    //         $groupIdee->delete('', DeleteIdeeAction::class);
    //         $groupIdee->put('', PutIdeeAction::class);
    //     });
    });
    $app->get('/occasion', GetOccasionAction::class);
    // TODO: renommer en /utilisateur/{idUtilisateur}
    $app->group('/profil', function (Group $groupProfil) {
        $groupProfil->get('', GetProfilAction::class);
        $groupProfil->put('', PutProfilAction::class);
    });
};
