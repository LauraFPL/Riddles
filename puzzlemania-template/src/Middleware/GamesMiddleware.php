<?php

namespace Salle\PuzzleMania\Middleware;

use FastRoute\Route;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Salle\PuzzleMania\Repository\TeamRepository;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

use Slim\Flash\Messages;
final class GamesMiddleware
{

    public function __construct(private TeamRepository $teamRepository, private Messages $flash)
    {
    }

    public function __invoke(Request $request, RequestHandler $next): Response
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        $name = $route->getName();
        if (!isset($_SESSION['user_id']))
        {
            $this->flash->addMessage("notifications", 'You have to be signed in to play.');
            $response = new Response();
            return $response->withHeader('Location','/sign-in')->withStatus(302);
        }
        if (!$this->teamRepository->getUserInTeams($_SESSION['user_id'])) {
            $this->flash->addMessage("notifications", 'You have to join a team.');
            $response = new Response();
            return $response->withHeader('Location','/join')->withStatus(302);
        }
        return $next->handle($request);
    }
}