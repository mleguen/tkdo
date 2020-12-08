<?php
declare(strict_types=1);

use App\Application\Actions\Connexion\CreateConnexionAction;
use App\Application\Actions\Idee\CreateIdeeAction;
use App\Application\Actions\Idee\ListIdeeAction;
use App\Application\Actions\Idee\CreateIdeeSuppressionAction;
use App\Application\Actions\Occasion\CreateOccasionAction;
use App\Application\Actions\Occasion\CreateParticipantOccasionAction;
use App\Application\Actions\Occasion\CreateResultatOccasionAction;
use App\Application\Actions\Occasion\EditOccasionAction;
use App\Application\Actions\Occasion\ListOccasionAction;
use App\Application\Actions\Occasion\ViewOccasionAction;
use App\Application\Actions\Utilisateur\CreateUtilisateurAction;
use App\Application\Actions\Utilisateur\CreateUtilisateurReinitMdpAction;
use App\Application\Actions\Utilisateur\EditUtilisateurAction;
use App\Application\Actions\Utilisateur\ListUtilisateurAction;
use App\Application\Actions\Utilisateur\ViewUtilisateurAction;
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
        $group->post('/{idIdee}/suppression', CreateIdeeSuppressionAction::class);
    });
    $app->group('/occasion', function (Group $group) {
        $group->get('', ListOccasionAction::class);
        $group->post('', CreateOccasionAction::class);
        $group->group('/{idOccasion}', function (Group $group) {
            $group->get('', ViewOccasionAction::class);
            $group->put('', EditOccasionAction::class);
            $group->post('/participant', CreateParticipantOccasionAction::class);
            $group->post('/resultat', CreateResultatOccasionAction::class);
        });
    });
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
