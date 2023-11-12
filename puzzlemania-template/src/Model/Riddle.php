<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Model;

use JsonSerializable;

class Riddle implements JsonSerializable
{

    private ?int $id;
    private ?int $userID;
    private string $riddle;
    private string $answer;


    /**
     * Static constructor / factory
     */
    public static function create(): Riddle
    {
        return new self();
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

    public function userID(): int
    {
        return $this->userID;
    }

    public function riddle(): string
    {
        return $this->riddle;
    }

    public function answer(): string
    {
        return $this->answer;
    }



    //parameter of type int that can be null
    public function setId(?int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function setUserId(?int $userID)
    {
        $this->userID = $userID;
        return $this;
    }

    public function setRiddle(string $riddle)
    {
        $this->riddle = $riddle;
        return $this;
    }

    public function setAnswer(string $answer)
    {
        $this->answer = $answer;
        return $this;
    }

  






}
