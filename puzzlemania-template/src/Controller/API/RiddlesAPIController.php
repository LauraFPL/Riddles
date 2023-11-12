<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller\API;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salle\PuzzleMania\Repository\RiddleRepository;
use Salle\PuzzleMania\Repository\UserRepository;
use Slim\Routing\RouteContext;
use Salle\PuzzleMania\Model\Riddle;

class RiddlesAPIController
{
    private RiddleRepository $riddleRepository;
    private UserRepository $userRepository;

    private CONST ADD_RIDDLE_MESSAGE = "'riddle' and/or 'answer' and/or 'userId' key missing";
    private CONST UPDATE_RIDDLE_MESSAGE = "The riddle and/or answer cannot be empty";
    private CONST UPDATE_RIDDLE_MESSAGE2 = "'riddle' and/or 'answer' key missing";
    private CONST RIDDLE_ANSWER_TOO_LONG_MESSAGE = "El 'riddle' o la 'answer' son demasiado largos";
    private CONST API_MESSAGE_PART1 = "Riddle with id ";
    private CONST API_MESSAGE_PART2 = " does not exist";
    private CONST RIDDLE_ID_INVALID_FORMAT = "The riddle id must be an integer";
    private CONST SERVER_ERROR = "Server database error, try again later";
    private CONST USER_ID_PROBLEM = "You can not create a riddle with this user";


    public function __construct(riddleRepository $riddleRepository, UserRepository $userRepository)
    {
        $this->riddleRepository = $riddleRepository;
        $this->userRepository = $userRepository;

    }

    public function getAllRiddles(Request $request, Response $response): Response
    {
        $data = $this->riddleRepository->getAllRiddles();

        $body = $response->getBody();
        $body->rewind();

        if($data != null){
            $body->write(json_encode($data));
            return $response->withStatus(200);
        }
            
        $body->write("{}");
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        
    }

    public function addRiddle(Request $request, Response $response): Response
    {

        $bodyRequest = $request->getParsedBody();
        
        $body = $response->getBody();
        $response->withHeader('Content-Type', 'application/json');

        if(empty($bodyRequest["userId"]) || empty($bodyRequest["riddle"]) || empty($bodyRequest["answer"]) ){
            
            $message = array("message" => self::ADD_RIDDLE_MESSAGE);
            $body->rewind();
            $body->write(json_encode($message));
            return $response->withStatus(400);
        }

        $riddle = Riddle::create()
            ->setUserId($bodyRequest["userId"])
            ->setRiddle($bodyRequest["riddle"])
            ->setAnswer($bodyRequest['answer']);


        if(strlen($riddle->riddle()) > 255 || strlen($riddle->answer()) > 255){
            $message = array("message" => self::RIDDLE_ANSWER_TOO_LONG_MESSAGE);
            $body->rewind();
            $body->write(json_encode($message));
            return $response->withStatus(412);
        }

        $user = $this->userRepository->getUserById($riddle->userID());
        if($user === null ){
            $message = array("message" => self::USER_ID_PROBLEM);
            $body->rewind();
            $body->write(json_encode($message));
            return $response->withStatus(412);
        }
        
        $riddleCreated = $this->riddleRepository->createRiddle($riddle);
        
        
        if($riddleCreated != null){
            $body->rewind();
            $body->write(json_encode($riddleCreated));
            return $response->withStatus(201);
        }else{
            $message = array("message" => self::SERVER_ERROR);
            $body->rewind();
            $body->write(json_encode(self::SERVER_ERROR));
            return $response->withStatus(500);
        }

    }

    public function getRiddle(Request $request, Response $response): Response
    {
        $riddleId = (int)$request->getAttribute('riddleId');
        $body = $response->getBody();
        $response->withHeader('Content-Type', 'application/json');

        if(self::validId($riddleId)){

            $riddle = $this->riddleRepository->getRiddleByID($riddleId);
            $body->rewind();

            if($riddle == null){   
                $message = array("message" => self::API_MESSAGE_PART1 . $riddleId . self::API_MESSAGE_PART2);
                $body->write(json_encode($message));
                return $response->withStatus(404);
            }

            $body->write(json_encode($riddle));
            return $response->withStatus(200);

        }

        $message = array("message" => self::RIDDLE_ID_INVALID_FORMAT);
        $body->rewind();
        $body->write(json_encode($message));
        return $response->withStatus(412);
        
    }

    public function updateRiddle(Request $request, Response $response): Response
    {
        $riddleId = (int)$request->getAttribute('riddleId');
        $bodyRequest = $request->getParsedBody();
        $body = $response->getBody();
        $response->withHeader('Content-Type', 'application/json');

        if(empty($bodyRequest["riddle"]) || empty($bodyRequest["answer"]) ) {
            $message = array("message" => self::UPDATE_RIDDLE_MESSAGE);
            $body->rewind();
            $body->write(json_encode($message));
            return $response->withStatus(400);
        }

        if(self::validId($riddleId)){

            if( $this->riddleRepository->getRiddleByID($riddleId) == null){
                $message = array("message" => self::API_MESSAGE_PART1 . $riddleId . self::API_MESSAGE_PART2);
                $body->write(json_encode($message));
                return $response->withStatus(404);
            }

            $riddle = Riddle::create()
            ->setRiddle($bodyRequest["riddle"])
            ->setAnswer($bodyRequest['answer']);

            if(strlen($riddle->riddle()) > 255 || strlen($riddle->answer())  > 255){
                $message = array("message" => self::RIDDLE_ANSWER_TOO_LONG_MESSAGE);
                $body->rewind();
                $body->write(json_encode($message));
                return $response->withStatus(412);
            }

            $riddleUpdated = $this->riddleRepository->updateRiddle($riddleId, $riddle);

            $response->getBody()->write(json_encode($riddleUpdated));
            return $response->withStatus(200);

        }

        
        $message = array("message" => self::RIDDLE_ID_INVALID_FORMAT);
        $body->rewind();
        $body->write(json_encode($message));
        return $response->withStatus(412);

    }

    public function deleteRiddle(Request $request, Response $response): Response
    {
        $riddleId = (int)$request->getAttribute('riddleId');
        $bodyRequest = $request->getParsedBody();
        $body = $response->getBody();
        $response->withHeader('Content-Type', 'application/json');

        if(self::validId($riddleId)){

            if( $this->riddleRepository->getRiddleByID($riddleId) == null){
                $message = array("message" => self::API_MESSAGE_PART1 . $riddleId . self::API_MESSAGE_PART2);
                $body->write(json_encode($message));
                return $response->withStatus(404);
            }

            $this->riddleRepository->deleteRiddle($riddleId);
            $message = array("message" => "Riddle with id $riddleId was successfully deleted");
            $response->getBody()->write(json_encode($message));
            return $response->withStatus(200);

        }

        $message = array("message" => self::RIDDLE_ID_INVALID_FORMAT);
        $body->rewind();
        $body->write(json_encode($message));
        return $response->withStatus(412);

    }

    private function validId($riddleId){

        if (empty($riddleId)){
            return false;
        }else if (!is_int($riddleId)){
            return false;
        }

        return true;
    }


}
