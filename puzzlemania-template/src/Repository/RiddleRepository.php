<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\Riddle;

interface RiddleRepository
{
    
    public function createRiddle(Riddle $riddle);
    public function getRiddleByID(int $id);
    public function getAllRiddles();
    public function getNumRiddle(); 

    public function updateRiddle(int $id, Riddle $riddle);
    public function deleteRiddle(int $id);

    
}
