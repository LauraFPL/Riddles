<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\Image;

interface ImageRepository
{
    public function createImageProfile(Image $image): void;
    public function getActualImageFromUser(int $id);
    public function getImagesFromUser(int $id);
    public function getImage(string $id);
}
