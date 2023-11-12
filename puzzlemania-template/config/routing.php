<?php

declare(strict_types=1);

use DI\Container;
use Salle\PuzzleMania\Controller\API\RiddlesAPIController;
use Salle\PuzzleMania\Controller\API\UsersAPIController;
use Salle\PuzzleMania\Controller\GamesController;
use Salle\PuzzleMania\Controller\SignUpController;
use Salle\PuzzleMania\Controller\SignInController;
use Salle\PuzzleMania\Controller\TeamsController;
use Salle\PuzzleMania\Controller\ProfileController;
use Salle\PuzzleMania\Controller\RiddlesController;
use Salle\PuzzleMania\Middleware\AuthorizationMiddleware;
use Salle\PuzzleMania\Middleware\TeamsMiddleware;
use Salle\PuzzleMania\Middleware\ImageProfileMiddleware;
use Salle\PuzzleMania\Middleware\GamesMiddleware;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;


function addRoutes(App $app, Container $container): void
{

    //LOGIN & REGISTER___________________________________________________________________________________________________________________________
    $app->get('/', SignInController::class . ':showHome')->setName('showHome');
    $app->get('/sign-in', SignInController::class . ':showSignInForm')->setName('signIn');
    $app->post('/sign-in', SignInController::class . ':signIn');

    $app->get('/sign-up', SignUpController::class . ':showSignUpForm')->setName('signUp');
    $app->post('/sign-up', SignUpController::class . ':signUp');

    
    //PROFILE____________________________________________________________________________________________________________________________________
    /*    $app->group('', function (RouteCollectorProxy $group) {
        $group->get('/profile', ProfileController::class . ':showProfile')->setName('profile');
        $group->post('/profile', ProfileController::class . ':changeProfileImage')->setName('profileImage')->add(ImageProfileMiddleware::class);
    })->add(AuthorizationMiddleware::class);*/

    $app->get('/profile', ProfileController::class . ':showProfile')->setName('profile')->add(AuthorizationMiddleware::class);
    $app->post('/profile', ProfileController::class . ':changeProfileImage')->setName('profileImage')->add(AuthorizationMiddleware::class)->add(ImageProfileMiddleware::class);


    //TEAMS______________________________________________________________________________________________________________________________________
    $app->group('', function (RouteCollectorProxy $group) {
        $group->get('/join', TeamsController::class . ':showJoins')->setName('join');
        $group->post('/join', TeamsController::class . ':teamAction');
        $group->get('/team-stats', TeamsController::class . ':showStats')->setName('stats');
    })->add(AuthorizationMiddleware::class)->add(TeamsMiddleware::class);
    
    //$app->get('/invite/join/{id:[0-9]+}', TeamsController::class . ':inviteJoin');
    $app->get('/invite/join/{id:[0-9]*}', TeamsController::class . ':inviteJoin')->add(TeamsMiddleware::class)->setName('invite');


    //GAMES______________________________________________________________________________________________________________________________________
    $app->get('/game', GamesController::class . ':showGameStart')->add(GamesMiddleware::class)->setName('game');
    $app->post('/game', GamesController::class . ':createGame')->add(GamesMiddleware::class);
    $app->get('/game/{gameId:[0-9]*}/riddle/{riddleId:[0-9]*}', GamesController::class . ':showGameRiddle')->add(GamesMiddleware::class)->setName('gameRiddle');
    $app->post('/game/{gameId:[0-9]*}/riddle/{riddleId:[0-9]*}', GamesController::class . ':checkGameRiddle')->add(GamesMiddleware::class);


    //API_______________________________________________________________________________________________________________________________________
        $app->group('/api', function (RouteCollectorProxy $group) {
        $group->get('/riddle', RiddlesAPIController::class . ':getAllRiddles')->setName('getRiddles');
        $group->post('/riddle', RiddlesAPIController::class . ':addRiddle')->setName('addRiddle');
        $group->get('/riddle/{riddleId:[0-9]*}', RiddlesAPIController::class . ':getRiddle')->setName('getRiddle');
        $group->put('/riddle/{riddleId:[0-9]*}', RiddlesAPIController::class . ':updateRiddle')->setName('updateRiddle');
        $group->delete('/riddle/{riddleId:[0-9]*}', RiddlesAPIController::class . ':deleteRiddle')->setName('deleteRiddle');
    });//->add(AuthorizationMiddleware::class);


    //RIDDLES____________________________________________________________________________________________________________________________________
    $app->get('/riddles', RiddlesController::class . ':showRiddles')->setName('showRiddles');
    $app->get('/riddles/{riddleId:[0-9]*}', RiddlesController::class . ':showRiddleById')->setName('showRiddle');
    
}
