<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use PDO;
use Salle\PuzzleMania\Model\Game;


use Salle\PuzzleMania\Repository\RiddleRepository;

final class MySQLGameRepository implements GameRepository
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';
    private PDO $databaseConnection;
    private RiddleRepository $riddleRepository;

    public function __construct(PDO $database, RiddleRepository $riddleRepository)
    {
        $this->databaseConnection = $database;
        $this->riddleRepository = $riddleRepository;
    }

    public function createGame(Game $game): int
    {
        $query = <<<'QUERY'
        INSERT INTO games(user_id, riddle1, riddle2, riddle3, points, playedAt)
        VALUES(:userId, :riddle1, :riddle2, :riddle3, :points, :playedAt)
        QUERY;

        $userId = $game->userId();
        $riddle1 = $game->riddles()[0]->id();
        $riddle2 = $game->riddles()[1]->id();
        $riddle3 = $game->riddles()[2]->id();
        $points = $game->points();
        $playedAt = $game->playedAt()->format(self::DATE_FORMAT);

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('userId', $userId, PDO::PARAM_INT);
        $statement->bindParam('riddle1', $riddle1, PDO::PARAM_INT);
        $statement->bindParam('riddle2', $riddle2, PDO::PARAM_INT);
        $statement->bindParam('riddle3', $riddle3, PDO::PARAM_INT);
        $statement->bindParam('points', $points, PDO::PARAM_INT);
        $statement->bindParam('playedAt', $playedAt, PDO::PARAM_STR);

        $statement->execute();
        return (int)$this->databaseConnection->lastInsertId();
    }

    public function getGameById(int $id)
    {
        $query = <<<'QUERY'
        SELECT * FROM games
        WHERE game_id = :id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_INT);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $rows = $statement->fetchAll();

            $game = Game::create();

            $game->setId($rows[0]['game_id']);
            $game->setUserId($rows[0]['user_id']);
            $game->setPoints($rows[0]['points']);
            $game->setPlayedAt(date_create_from_format(self::DATE_FORMAT, $rows[0]['playedAt']));

            $riddle1 = $this->riddleRepository->getRiddleById($rows[0]['riddle1']);
            $riddle2 = $this->riddleRepository->getRiddleById($rows[0]['riddle2']);
            $riddle3 = $this->riddleRepository->getRiddleById($rows[0]['riddle3']);

            $game->setRiddles([$riddle1, $riddle2, $riddle3]);

            return $game;
        } else {
            return null;
        }
    }

    public function updateGame(Game $game): void {
        $query = <<<'QUERY'
        UPDATE games 
        SET points = :points, playedAt = :playedAt
        WHERE game_id = :gameID 
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $points = $game->points();
        $gameId = $game->id();
        $playedAt = $game->playedAt()->format(self::DATE_FORMAT);

        $statement->bindParam('points', $points, PDO::PARAM_INT);
        $statement->bindParam('gameID', $gameId, PDO::PARAM_INT);
        $statement->bindParam('playedAt', $playedAt, PDO::PARAM_STR);

        $statement->execute();
    }

    public function checkAnswer(int $gameID, int $riddleID, string $answer): bool
    {
        $query = <<<'QUERY'
        SELECT * FROM riddles
        WHERE riddle_id = :riddleID
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('riddleID', $riddleID, PDO::PARAM_INT);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $rows = $statement->fetchAll();
            if (strcasecmp($rows[0]['answer'], $answer) == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}