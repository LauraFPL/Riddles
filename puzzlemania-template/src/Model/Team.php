<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Model;

use JsonSerializable;

class Team implements JsonSerializable
{

    private int $id;
    private string $name;
    private User $user1;
    private User $user2;
    private int $lastGamePoints;

    /**
     * Static constructor / factory
     */
    public static function create(): Team
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

    public function name(): string
    {
        return $this->name;
    }

    public function users(): array
    {
        $users[0] = $this->user1;
        if (isset($this->user2)){$users[1] = $this->user2;}
        return $users;
    }

    public function lastGamePoints(): int
    {
        return $this->lastGamePoints;
    }

    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setUser1(User $user1)
    {
        $this->user1 = $user1;
        return $this;
    }

    public function setUser2(User $user2)
    {
        $this->user2 = $user2;
        return $this;
    }

    public function setLastGamePoints(int $points)
    {
        $this->lastGamePoints = $points;
        return $this;
    }

}
