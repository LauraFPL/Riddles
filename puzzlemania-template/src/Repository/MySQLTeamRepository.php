<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use PDO;
use Salle\PuzzleMania\Model\Team;
use Salle\PuzzleMania\Model\User;

use Salle\PuzzleMania\Repository\UserRepository;

final class MySQLTeamRepository implements TeamRepository
{
    private PDO $databaseConnection;
    private UserRepository $userRepository;

    public function __construct(PDO $database, UserRepository $userRepository)
    {
        $this->databaseConnection = $database;
        $this->userRepository = $userRepository;
    }

    public function createTeam(Team $team): void
    {
        $query = <<<'QUERY'
        INSERT INTO teams(name, user1)
        VALUES(:name, :user1)
        QUERY;


        $name = $team->name();
        $user1 = $team->users()[0]->getId();

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('name', $name, PDO::PARAM_STR);
        $statement->bindParam('user1', $user1, PDO::PARAM_INT);

        $statement->execute();
    }

    public function getIncompleteTeams()
    {
        $query = <<<'QUERY'
        SELECT * FROM teams
        WHERE user1 IS null OR user2 IS null
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->execute();

        $teams = [];

        $count = $statement->rowCount();
        if ($count > 0) {
            $rows = $statement->fetchAll();

            for ($i = 0; $i < $count; $i++) {
                $user1 = User::create()
                ->setId($rows[$i]['user1']);
                $team = Team::create()
                    ->setId(intval($rows[$i]['id']))
                    ->setName($rows[$i]['name'])
                    ->setUser1($user1)
                    ->setLastGamePoints($rows[$i]['lastGamePoints']);
                $teams[] = $team;
            }
        }
        return $teams;
    }

    public function updateTeam(Team $team): void
    {
        $query = <<<'QUERY'
        UPDATE teams 
        SET user2 = :user2 
        WHERE id = :teamID
        QUERY;


        $id = $team->id();
        $user2 = $team->users()[1]->getId();

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('user2', $user2, PDO::PARAM_INT);
        $statement->bindParam('teamID', $id, PDO::PARAM_INT);

        $statement->execute();
    }

    public function updateTeamByEmail(int $teamID, string $email): void
    {
        $query = <<<'QUERY'
        UPDATE teams
        LEFT JOIN users 
        ON users.email = :email
        SET teams.user2 = users.id  
        WHERE teams.id = :id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('id', $teamID, PDO::PARAM_INT);

        $statement->execute();
    }

    public function getTeamByID(int $id){
        $query = <<<'QUERY'
        SELECT teams.id, name, user1, user1.email AS 'user1.email', user2, user2.email AS 'user2.email', lastGamePoints 
        FROM teams
        LEFT JOIN users AS user1
        ON teams.user1 = user1.id
        LEFT JOIN users AS user2
        ON teams.user2 = user2.id
        WHERE teams.id = :id
        LIMIT 1
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_INT);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch();
            return $this->getTeam($row);
        }
        return null;
    }
    
    public function getTeamByUserId(int $userID){
        $query = <<<'QUERY'
        SELECT teams.id, name, user1, user1.email AS 'user1.email', user2, user2.email AS 'user2.email', lastGamePoints 
        FROM teams
        LEFT JOIN users AS user1
        ON teams.user1 = user1.id
        LEFT JOIN users AS user2
        ON teams.user2 = user2.id
        WHERE user1.id = :id OR user2.id = :id
        LIMIT 1
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id', $userID, PDO::PARAM_INT);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch();
            return $this->getTeam($row);
        }
        return null;
    }

    private function getTeam($row){
        $lastGamePoints = $this->getLastGamePoints();
            if (isset($row['user2']) && $row['user2'] != null){
                $user1 = User::create()
                ->setId(intval($row['user1']))
                ->setEmail($row['user1.email']);
                $user2 = User::create()
                ->setId(intval($row['user2']))
                ->setEmail($row['user2.email']);
                $team = Team::create()
                ->setId($row['id'])
                ->setName($row['name'])
                ->setUser1($user1)
                ->setUser2($user2)
                ->setLastGamePoints($lastGamePoints);
            } else {
                $user1 = User::create()
                ->setId(intval($row['user1']))
                ->setEmail($row['user1.email']);
                $team = Team::create()
                ->setId($row['id'])
                ->setName($row['name'])
                ->setUser1($user1)
                ->setLastGamePoints($lastGamePoints);
            }
            return $team;
    }

    public function getUserInTeams(int $id): bool
    {
        $query = <<<'QUERY'
        SELECT * FROM teams
        WHERE user1 = :id OR user2 = :id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_INT);

        $statement->execute();

        $count = $statement->rowCount();
        return $count > 0;
    }

    public function getLastGamePoints()
    {
        $query = <<<'QUERY'
            SELECT points FROM teams
            LEFT JOIN users AS user1
            ON teams.user1 = user1.id
            LEFT JOIN users AS user2
            ON teams.user2 = user2.id
            LEFT JOIN games
            ON games.user_id = user1.id OR games.user_id = user2.id
            ORDER BY games.playedAt DESC
            LIMIT 1
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch();
            return (int)$row['points'];
        }
        return null;


    }


}
