<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Controller;

use Salle\PuzzleMania\Service\ValidatorService;

use Salle\PuzzleMania\Repository\TeamRepository;
use Salle\PuzzleMania\Repository\UserRepository;

use Salle\PuzzleMania\Model\Team;
use Salle\PuzzleMania\Model\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Slim\Flash\Messages;


final class TeamsController
{

    private ValidatorService $validator;

    public function __construct(
        private Twig $twig,
        private Messages $flash,
        private TeamRepository $teamRepository,
        private UserRepository $userRepository
    )
    {
        $this->validator = new ValidatorService();
    }

    /**
     * Renders the form
     */
    public function showJoins(Request $request, Response $response): Response
    {
        $messages = $this->flash->getMessages();

        $notifications = $messages['notifications'] ?? [];
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        return $this->twig->render(
            $response,
            'teams.twig',
            [
                'formAction' => $routeParser->urlFor('join'),
                'teams' => $this->teamRepository->getIncompleteTeams(),
                'notifs' => $notifications
            ]
        );
    }

    public function teamAction(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        if (isset($data['join'])){
            return $this->joinTeam($request, $response);
        } else {
            return $this->createTeam($request, $response);
        }

        
    }

    public function createTeam(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $errors = [];

        $errors['name'] = $this->validator->validateTeamName($data['name']);

        // Unset variables if there are no errors
        if ($errors['name'] == '') {
            unset($errors['name']);
        }

        $user1 = $this->userRepository->getUserById($_SESSION['user_id']);

        if (count($errors) == 0) {
            $team = Team::create()
                ->setName($data['name'])
                ->setUser1($user1);
            $this->teamRepository->createTeam($team);
            return $response->withHeader('Location', '/team-stats')->withStatus(302);
        }
        return $this->twig->render(
            $response,
            'teams.twig',
            [
                'formErrors' => $errors,
                'formData' => $data,
                'formAction' => $routeParser->urlFor('join'),
                'teams' => $this->teamRepository->getIncompleteTeams()
            ]
        );
    }

    public function joinTeam(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();


        $errors = [];

        $user2 = User::create()->setId(intval($_SESSION['user_id']));
        $team = Team::create()->setId(intval($data['join']));   
        $team->setUser1($user2); //Esto esta de placeholder porque no se tiene que comprobar
        $team->setUser2($user2);

        if (count($errors) == 0) {

            $this->teamRepository->updateTeam($team);
            return $response->withHeader('Location', '/team-stats')->withStatus(302);
        }
        return $this->twig->render(
            $response,
            'teams.twig',
            [
                'formErrors' => $errors,
                'formData' => $data,
                'formAction' => $routeParser->urlFor('join'),
                'teams' => $this->teamRepository->getIncompleteTeams()
            ]
        );
    }

    public function inviteJoin(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $route = $request->getUri()->getPath();
        $parts = explode('/', $route);
        $id = intval(array_pop($parts));

        if ($this->teamRepository->getTeamByID($id) != null)
        {
            $response = $this->twig->render(
                $response,
                'sign-up.twig',
                [
                    'formData' => $data,
                    'formAction' => $routeParser->urlFor('signUp'),
                    'teamID' => $id
                ]
            );
        } else {
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        
        return $response;
    }

    public function showStats(Request $request, Response $response): Response
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $team = $this->teamRepository->getTeamByUserId($_SESSION['user_id']);
        $usernames = [];
        $users = $team->users();
        $usernames[] = explode('@', $users[0]->email())[0];
        if (isset($users[1])) { 
            $usernames[] = explode('@', $users[1]->email())[0];
        }

        $data = 
        [
            'symbology' => 'DINSpecQRCode',
            'QRCodeErrorCorrectionLevel ' => 'Q',
            'QRCodeModuleSize' => '0.1',
            'Code' => 'http://localhost:8030/invite/join/' . $team->id()
        ];
          
          $options = array(
            'http' => array(
              'method'  => 'POST',
              'content' => json_encode( $data ),
              'header' =>  "Content-Type: application/json\r\n" .
                          "Accept: image/png\r\n"
              )
          );
          
          $context  = stream_context_create( $options );
          $url = 'http://pw_barcode:80/BarcodeGenerator';
          $api_response = file_get_contents( $url, false, $context );
          $img = 'data:image/png;base64,' . base64_encode($api_response);

          $messages = $this->flash->getMessages();

          $notifications = $messages['notifications'] ?? [];


          return $this->twig->render(
            $response,
            'stats.twig',
            [
                'formAction' => $routeParser->urlFor('stats'),
                'team' => $team->jsonSerialize(),
                'usernames' => $usernames,
                'members' => count($usernames),
                'qr' => $img,
                'notifs' => $notifications
            ]
        );

    }

    


}