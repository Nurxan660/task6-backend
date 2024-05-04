<?php

namespace App\DTO;


use App\Entity\Board;

class BoardCreationResponse
{
    private string $boardName;
    private string $uuid;
    private string $status;

    public function __construct(Board $board, string $status)
    {
        $this->boardName = $board->getName();
        $this->uuid = $board->getUuid();
        $this->status = $status;
    }

    public function getBoardName(): string
    {
        return $this->boardName;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
    }


}