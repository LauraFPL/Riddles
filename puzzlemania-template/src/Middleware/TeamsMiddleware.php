<?php
//We use this instead of the checking if the user is logged in every single controller.
//To avoid using that function in every call of our application
//We create a middleware that will start the session for us.
namespace Salle\PuzzleMania\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

use Slim\Flash\Messages;

use Salle\PuzzleMania\Repository\TeamRepository;

final class TeamsMiddleware
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
            return $next->handle($request);
        }

        if ($this->teamRepository->getUserInTeams($_SESSION['user_id']) && ( $name === "join" || $name === "invite")) {
            $this->flash->addMessage("notifications", 'You have already joined a team.');
            $response = new Response();
            return $response->withHeader('Location','/team-stats')->withStatus(302);
        } else if (!$this->teamRepository->getUserInTeams($_SESSION['user_id']) && $name === "stats" )
        {
            $this->flash->addMessage("notifications", 'You have to join a team first.');
            $response = new Response();
            return $response->withHeader('Location','/join')->withStatus(302);
        }

        return $next->handle($request);
        
    }
}