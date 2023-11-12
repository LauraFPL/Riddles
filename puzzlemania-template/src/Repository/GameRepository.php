<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;
use Salle\PuzzleMania\Model\Game;


interface GameRepository
{
    public function createGame(Game $game): int;
    public function getGameById(int $id);
    public function updateGame(Game $game): void;
    public function checkAnswer(int $gameID, int $riddleID, string $answer): bool;

}