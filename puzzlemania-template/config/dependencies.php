<?php

declare(strict_types=1);
use Psr\Container\ContainerInterface;

//LLibreries____________________________________________________________
use Slim\Flash\Messages;
use Slim\Views\Twig;
use GuzzleHttp\Client;

//Models/base de dades__________________________________________________
use Salle\PuzzleMania\Repository\MySQLRiddleRepository;
use Salle\PuzzleMania\Repository\MySQLUserRepository;
use Salle\PuzzleMania\Repository\MySQLTeamRepository;
use Salle\PuzzleMania\Repository\MySQLImageRepository;
use Salle\PuzzleMania\Repository\PDOConnectionBuilder;
use Salle\PuzzleMania\Repository\MySQLGameRepository;

//Controllers___________________________________________________________
use Salle\PuzzleMania\Controller\API\RiddlesAPIController;
use Salle\PuzzleMania\Controller\API\UsersAPIController;
use Salle\PuzzleMania\Controller\GamesController;
use Salle\PuzzleMania\Controller\SignUpController;
use Salle\PuzzleMania\Controller\SignInController;
use Salle\PuzzleMania\Controller\TeamsController;
use Salle\PuzzleMania\Controller\ProfileController;
use Salle\PuzzleMania\Controller\RiddlesController;


//Middlewares___________________________________________________________
use Salle\PuzzleMania\Middleware\TeamsMiddleware;
use Salle\PuzzleMania\Middleware\ImageProfileMiddleware;
use Salle\PuzzleMania\Middleware\GamesMiddleware;
use Salle\PuzzleMania\Middleware\AuthorizationMiddleware;

function addDependencies(ContainerInterface $container): void
{

    //LLibreries___________________________________________________________________________________________________
        $container->set(
            'view',
            function () {
                return Twig::create(__DIR__ . '/../templates', ['cache' => false]);
            }
        );

        $container->set('db', function () {
            $connectionBuilder = new PDOConnectionBuilder();
            return $connectionBuilder->build(
                $_ENV['MYSQL_ROOT_USER'],
                $_ENV['MYSQL_ROOT_PASSWORD'],
                $_ENV['MYSQL_HOST'],
                $_ENV['MYSQL_PORT'],
                $_ENV['MYSQL_DATABASE']
            );
        });

        $container->set(
            'flash',
            function () {
                return new Messages();
            }
        );

        $container->set(
            'guzzleAPI', 
            function(){ 
                return new Client(['base_uri' => 'http://pw_local-serverPractica:80']);
            }
        );



    //Models/base de dades_________________________________________________________________________________________
        $container->set('user_repository', function (ContainerInterface $container) {
            return new MySQLUserRepository($container->get('db'));
        });
    
        $container->set('team_repository', function (ContainerInterface $container) {
            return new MySQLTeamRepository($container->get('db'), $container->get('user_repository'));
        });

        $container->set('image_repository', function (ContainerInterface $container) {
            return new MySQLImageRepository($container->get('db'));
        });
        
        $container->set('riddle_repository', function (ContainerInterface $container) {
            return new MySQLRiddleRepository($container->get('db'));
        });

        $container->set('game_repository', function (ContainerInterface $container) {
            return new MySQLGameRepository($container->get('db'), $container->get('riddle_repository'));
        });



    //Controllers__________________________________________________________________________________________________
        $container->set(
            SignInController::class,
            function (ContainerInterface $c) {
                return new SignInController($c->get('view'), $c->get('user_repository'), $c->get("flash"));
            }
        );

        $container->set(
            SignUpController::class,
            function (ContainerInterface $c) {
                return new SignUpController($c->get('view'), $c->get('user_repository'), $c->get('team_repository'));
            }
        );

        $container->set(
            ProfileController::class,
            function (ContainerInterface $c) {
                return new ProfileController($c->get("view"), $c->get("flash"), $c->get("user_repository"), $c->get("image_repository"));
            }
        );

        $container->set(
            TeamsController::class,
            function (ContainerInterface $c) {
                return new TeamsController($c->get('view'), $c->get("flash"), $c->get('team_repository'), $c->get('user_repository'));
            }
        );

        $container->set(
            RiddlesAPIController::class,
            function (ContainerInterface $c) {
                return new RiddlesAPIController($c->get('riddle_repository'), $c->get('user_repository'));
            }
        );

        $container->set(
            RiddlesController::class,
            function (ContainerInterface $c) {
                return new RiddlesController($c->get('view'), $c->get("guzzleAPI"));
            }
        );
        
    //Middlewares___________________________________________________________
        $container->set(AuthorizationMiddleware::class, function (ContainerInterface $container) {
            return new AuthorizationMiddleware($container->get('flash'));
        });
    
        $container->set(
            ImageProfileMiddleware::class,
            function (ContainerInterface $c) {
                return new ImageProfileMiddleware($c->get("flash"));
            }
        );
    
        $container->set(
            TeamsMiddleware::class,
            function (ContainerInterface $c) {
                return new TeamsMiddleware($c->get('team_repository'), $c->get("flash"));
            }
        );

        $container->set(
            GamesController::class,
            function (ContainerInterface $c) {
                return new GamesController($c->get('view'), $c->get("flash"), $c->get('game_repository') ,$c->get('riddle_repository'),$c->get('team_repository') );
            }
        );

        $container->set(
            GamesMiddleware::class,
            function (ContainerInterface $c) {
                return new GamesMiddleware($c->get('team_repository'), $c->get("flash"));
            }
        );
}
