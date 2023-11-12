<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use PDO;
use Salle\PuzzleMania\Model\Image;
use Salle\PuzzleMania\Model\Team;
use Salle\PuzzleMania\Model\User;

final class MySQLImageRepository implements ImageRepository
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';
    private PDO $databaseConnection;

    public function __construct(PDO $database)
    {
        $this->databaseConnection = $database;
    }

    public function createImageProfile(Image $image): void{
        
        $id = $image->id();
        $originalName = $image->originalName();
        $width = $image->width();
        $height = $image->height();
        $userUpload = $image->userUpload();
        $actualInUse = $image->actualInUse();
        $uploadedAt = $image->uploadedAt()->format(self::DATE_FORMAT);;;
        

        $query1 = <<<'QUERY'
        UPDATE images 
        SET actualInUse = false
        WHERE userUpload = :userUpload;
        QUERY;

        $statementUpdate = $this->databaseConnection->prepare($query1);
        $statementUpdate->bindParam('userUpload', $userUpload, PDO::PARAM_INT);
        $statementUpdate->execute();



        $query2 = <<<'QUERY'
        INSERT INTO images(id, originalName, width, height, userUpload, actualInUse, uploadedAt)
        VALUES(:id, :originalName, :width, :height, :userUpload, :actualInUse, :uploadedAt)
        QUERY;

        $statement = $this->databaseConnection->prepare($query2);

        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->bindParam('originalName', $originalName, PDO::PARAM_STR);
        $statement->bindParam('width', $width, PDO::PARAM_INT);
        $statement->bindParam('height', $height, PDO::PARAM_INT);
        $statement->bindParam('userUpload', $userUpload, PDO::PARAM_INT);
        $statement->bindParam('actualInUse', $actualInUse, PDO::PARAM_BOOL);
        $statement->bindParam('uploadedAt', $uploadedAt, PDO::PARAM_STR);

        $statement->execute();
    }
    public function getActualImageFromUser(int $id){

        $query = <<<'QUERY'
        SELECT * FROM images WHERE userUpload = :id AND actualInUse = true
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_INT);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch();
            $image = new Image($row['id'], $row['originalName'], 
                               $row['width'], $row['height'], 
                               $row['userUpload'], (boolean) $row['actualInUse'], 
                               $row['uploadedAt']);
                
            return $image;
        }
        return null;

    }
    public function getImagesFromUser(int $id){
        
    }

    public function getImage(string $id){
        $query = <<<'QUERY'
        SELECT * FROM images WHERE id = :id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_INT);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch();
            $image = new Image($row['id'], $row['originalName'], 
                               $row['width'], $row['height'], 
                               $row['userUpload'], $row['actualInUse'], 
                               $row['uploadedAt']);
                
            return $image;
        }
        return null;
    }
}