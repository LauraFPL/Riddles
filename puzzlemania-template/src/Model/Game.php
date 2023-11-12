<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Model;

use DateTime;
use JsonSerializable;

class Game implements JsonSerializable
{

    private int $id;
    private int $userId;
    private $riddles = array();
    private int $points = 10;
    private DateTime $playedAt;

    /**
     * Static constructor / factory
     */
    public static function create(): Game
    {
        $game = new self();
        return $game;
    }

    /**
     * Function called when encoded with json_encode
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function id(): int
    {
        return $this->id;
    }

    public function userId(): int
    {
        if (isset($_SESSION['user_id'])) {
            $this->userId = (int) $_SESSION['user_id'];
        }
        return $this->userId;
    }
    public function riddles(): array
    {
        return $this->riddles;
    }

    public function points(): int
    {
        return $this->points;
    }

    public function playedAt(): DateTime
    {
        return new DateTime();
    }
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function setRiddles(array $riddles)
    {
        $this->riddles = $riddles;
        return $this;
    }

    public function setPoints(int $points)
    {
        $this->points = $points;
        return $this;
    }

    public function addPoints(int $points)
    {
        $this->points += $points;
        return $this;
    }

    public function setPlayedAt(DateTime $playedAt)
    {
        $this->playedAt = $playedAt;
        return $this;
    }



    

}
