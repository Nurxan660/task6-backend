<?php

namespace App\Service;

use App\Entity\Board;
use Doctrine\ORM\EntityManagerInterface;

class BoardService
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function createBoard(string $boardName): Board
    {
        $board = new Board();
        $board->setName($boardName);
        $this->entityManager->persist($board);
        $this->entityManager->flush();
        return $board;
    }
}