<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Model;

use DateTime;
use JsonSerializable;

class Image implements JsonSerializable
{

    private string $id;
    private string $originalName;
    private int $width;
    private int $height;
    private int $userUpload;
    private bool $actualInUse;
    private DateTime $uploadedAt;

    /**
     * Static constructor / factory
     */
    public function __construct(string $id, string $originalName, int $width, int $height, int $userUpload, bool $actualInUse = null, string $uploadedAt = null){
        $this->id = $id;
        $this->originalName = $originalName;
        $this->width = $width;
        $this->height = $height;
        $this->userUpload = $userUpload;

        if($actualInUse == null){ 
            $this->actualInUse = true;
        }else{ 
            $this->actualInUse = $actualInUse;
        }

        if($uploadedAt == null){ 
            $this->uploadedAt = new DateTime('now');
        }else{ 
            $this->uploadedAt = new DateTime($uploadedAt);
        }
        
    }
    /**
     * Function called when encoded with json_encode
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function originalName(): string
    {
        return $this->originalName;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function userUpload(){
        return $this->userUpload;
    }
    public function actualInUse(){
        return $this->actualInUse;
    }
    public function uploadedAt()
    {
        return $this->uploadedAt;
    }


}
