<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;
use GuzzleHttp\Client;

class RiddlesController
{

    public function __construct(private Twig $twig,private $guzzleAPI){}

    public function showRiddles(Request $request, Response $response): Response
    {
        $riddles = $this->guzzleAPI->request('GET', "/api/riddle");
        $riddles = json_decode((string) $riddles->getBody());

        return $this->twig->render($response, 'riddles.twig', [
            'riddles' => $riddles
        ]);

       
    }

    public function showRiddleById(Request $request, Response $response): Response
    {
        $riddleId = (int)$request->getAttribute('riddleId');

        try{
            
            $riddle = $this->guzzleAPI->request('GET', "/api/riddle/$riddleId");

            $riddle = json_decode((string) $riddle->getBody());
            return $this->twig->render($response, 'riddle.twig', [
                'riddle' => $riddle
            ]);
          
        }catch (Exception $e){

            return $this->twig->render($response, 'riddle.twig', [
                'riddle' => []
            ]);

        }

        

    }

}
