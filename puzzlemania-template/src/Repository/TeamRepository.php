<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\User;
use Salle\PuzzleMania\Model\Team;

use Salle\PuzzleMania\Repository\UserRepository;

interface TeamRepository
{
    public function createTeam(Team $team): void;
    public function getIncompleteTeams();
    public function updateTeam(Team $team): void;
    public function getTeamByID(int $id);
    public function getTeamByUserId(int $userID);
    public function updateTeamByEmail(int $teamID, string $email): void;
    public function getUserInTeams(int $id): bool;

}
