<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Controller;

use Slim\Flash\Messages;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

use Salle\PuzzleMania\Model\Game;
use Salle\PuzzleMania\Model\Riddle;
use Salle\PuzzleMania\Repository\GameRepository;
use Salle\PuzzleMania\Repository\RiddleRepository;
use Salle\PuzzleMania\Repository\TeamRepository;

final class GamesController
{

    
    public function __construct(
        private Twig $twig,
        private Messages $flash,
        private GameRepository $gameRepository,
        private RiddleRepository $riddleRepository,
        private TeamRepository $teamRepository
    )
    {}

    public function showGameStart(Request $request, Response $response): Response {

        $messages = $this->flash->getMessages();

        $notifications = $messages['notifications'] ?? [];
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        return $this->twig->render(
            $response,
            'game-start.twig',
            [
                'formAction' => $routeParser->urlFor('game')
            ]
        );

    }

    public function createGame(Request $request, Response $response): Response{

        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $errors = [];
        
        //generate Game
        $game = Game::create();

        //Get Riddles and select 3 random to add to the game
        $riddles = $this->riddleRepository->getAllRiddles();

        //Check if there's enough riddles to start a game.
        if (!isset($riddles)) {
            $errors['game'] = 'There are not enought riddles to start a game';
        } else if (count($riddles) < 3){
            $errors['game'] = 'There are not enought riddles to start a game';
        }
        
        //If there's no errores we continue
        if (count($errors) == 0){

            //Selecting random riddles for the game
            shuffle($riddles);
            $rand_riddles = array_rand($riddles, 3);
            $game->setRiddles(
                [
                    $riddles[$rand_riddles[0]],
                    $riddles[$rand_riddles[1]], 
                    $riddles[$rand_riddles[2]]
                ]);
    
            
            $game->setId($this->gameRepository->createGame($game));
            //Save Game id in session so that we can get it later
            //$_SESSION['game_id'] = $game->id();
            //And save game in database
            //var_dump($this->game);
            //redirect to game riddle
            $path = '/game/'.$game->id().'/riddle/'. 1;
            //var_dump($path);
            return $response->withHeader('Location', $path)->withStatus(302);
        }

        // If there's an error:
        return $this->twig->render(
            $response,
            'game-start.twig',
            [
                'formAction' => $routeParser->urlFor('game'),
                'formErrors' => $errors,
            ]
        );

    }

    public function showGameRiddle(Request $request, Response $response): Response {

        $messages = $this->flash->getMessages();

        $notifications = $messages['notifications'] ?? [];
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        //first get the game from the database
        $game = $this->gameRepository->getGameById((int)$request->getAttribute('gameId'));

        $riddle = $game->riddles()[(int)$request->getAttribute('riddleId')-1];

        return $this->twig->render(
            $response,
            'game-riddle.twig',
            [
                'formAction' => $routeParser->urlFor('gameRiddle', ['gameId' => $game->id(), 'riddleId' => (int)$request->getAttribute('riddleId')]),
                'riddle' => $riddle->riddle(),
                'points' => $game->points(),
            ]
        
        );

    }

    public function checkGameRiddle(Request $request, Response $response): Response {
        
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $data = $request->getParsedBody();
        $answer = $data['answer'];
        $gameId = (int)$request->getAttribute('gameId');
        $riddleId = (int)$request->getAttribute('riddleId') - 1;
        $game = $this->gameRepository->getGameById((int)$request->getAttribute('gameId'));
      
        $riddleRealId = $game->riddles()[$riddleId]->id();
        if ($this->gameRepository->checkAnswer($gameId, $riddleRealId, $answer)) {
            $result = true;
            $game->addPoints(10);
            $riddleCompleted = true;
        } else {
            $result = false;
            $game->addPoints(-10);
            $riddleCompleted = true;
        }
        

        $this->gameRepository->updateGame($game);
        
        $url = $routeParser->urlFor('gameRiddle', ['gameId' => $game->id(), 'riddleId' => ((int)$request->getAttribute('riddleId') + 1)]);
        if ($riddleId == 2)
        {
            $end = "You completed all the riddles!";

        } else if($game->points() <= 0) {
            $end = "You lost all your points!";
        }
        if (isset($end)){
            return $this->twig->render(
                $response,
                'game-riddle.twig',
                [
                    'formAction' => $routeParser->urlFor('gameRiddle', ['gameId' => $game->id(), 'riddleId' => (int)$request->getAttribute('riddleId')]),
                    'riddle' => $game->riddles()[$riddleId]->riddle(),
                    'answer' => $answer,
                    'rightAnswer'=> $game->riddles()[$riddleId]->answer(),
                    'result' => $result,
                    'url' => $url,
                    'end' => $end,
                    'points' => $game->points()
                ]
            
            );
        }else{
            return $this->twig->render(
                $response,
                'game-riddle.twig',
                [
                    'formAction' => $routeParser->urlFor('gameRiddle', ['gameId' => $game->id(), 'riddleId' => (int)$request->getAttribute('riddleId')]),
                    'riddle' => $game->riddles()[$riddleId]->riddle(),
                    'answer' => $answer,
                    'rightAnswer'=> $game->riddles()[$riddleId]->answer(),
                    'result' => $result,
                    'url' => $url,
                    'points' => $game->points()
                ]
            
            );
        }

        
    }


}