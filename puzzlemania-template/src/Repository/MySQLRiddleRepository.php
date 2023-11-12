<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use PDO;
use Salle\PuzzleMania\Model\Riddle;

use Salle\PuzzleMania\Repository\RiddleRepository;

final class MySQLRiddleRepository implements RiddleRepository
{
    private PDO $databaseConnection;

    public function __construct(PDO $database,)
    {
        $this->databaseConnection = $database;
    }

    public function createRiddle(Riddle $riddle):riddle
    {
        $query = <<<'QUERY'
        INSERT INTO riddles(user_id, riddle, answer)
        VALUES(:userID, :question, :answer);
        QUERY;

        $userID = $riddle->userID();
        $question = $riddle->riddle();
        $answer = $riddle->answer();

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('userID', $userID, PDO::PARAM_INT);
        $statement->bindParam('question', $question, PDO::PARAM_STR);
        $statement->bindParam('answer', $answer, PDO::PARAM_STR);

        $statement->execute();

        $query1 = <<<'QUERY'
        SELECT * FROM riddles WHERE riddle_id = LAST_INSERT_ID();
        QUERY;

        $statement1 = $this->databaseConnection->prepare($query1);
        $statement1->execute();

        if ($statement1->rowCount() > 0){
            $row = $statement1->fetch();
            $riddle = Riddle::create()
            ->setId($row['riddle_id'])
            ->setUserId($row['user_id'])
            ->setRiddle($row['riddle'])
            ->setAnswer($row['answer']);
            return $riddle;

        }

        return null;

    }

    public function getRiddleByID(int $id)
    {
        $query = <<<'QUERY'
        SELECT * FROM riddles
        WHERE riddle_id = :id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_INT);

        $statement->execute();

        if ($statement->rowCount() > 0){
            $row = $statement->fetch();
            $riddle = Riddle::create()
            ->setId($row['riddle_id'])
            ->setUserId($row['user_id'])
            ->setRiddle($row['riddle'])
            ->setAnswer($row['answer']);
            return $riddle;

        }

        return null;
        
    }

    public function getAllRiddles()
    {
        $query = <<<'QUERY'
        SELECT * FROM riddles
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->execute();

        
        if ($statement->rowCount() > 0){
            $rows = $statement->fetchAll();
            $riddles = array();

            foreach ($rows as $key => $value) {
                $riddle = Riddle::create()
                ->setId($value['riddle_id'])
                ->setUserId($value['user_id'])
                ->setRiddle($value['riddle'])
                ->setAnswer($value['answer']);
                 $riddles[]=$riddle;
            }

            return $riddles;
        }

        return null;
    }

    public function getNumRiddle()
    {
        $query = <<<'QUERY'
        SELECT * FROM riddles
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->execute();
        return $statement->rowCount();
    }

    public function updateRiddle(int $id, Riddle $riddle): Riddle{

        $query = <<<'QUERY'
        UPDATE riddles 
        SET riddle = :riddle, answer = :answer
        WHERE riddle_id = :id
        QUERY;

        $acertijo = $riddle->riddle();
        $answer = $riddle->answer();

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('riddle', $acertijo, PDO::PARAM_STR);
        $statement->bindParam('answer', $answer, PDO::PARAM_STR);
        $statement->bindParam('id', $id, PDO::PARAM_INT);

        $statement->execute();

        $riddleUpdated = self::getRiddleByID($id);

        return $riddleUpdated;
    }
    public function deleteRiddle(int $id){

        $query = <<<'QUERY'
        DELETE FROM riddles WHERE riddle_id = :id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('id', $id, PDO::PARAM_INT);

        $statement->execute();

        return;
        
    }

}