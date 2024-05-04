<?php

namespace App\DTO;

use Symfony\Component\HttpFoundation\RequestStack;

class BoardCreationRequest
{
    private ?string $boardName;

    public function __construct(RequestStack $requestStack)
    {
        $query = $requestStack->getCurrentRequest();
        $content = $query->getContent();
        $data = json_decode($content, true);
        $this->boardName = $data['boardName'] ?? null;
    }

    public function getBoardName(): ?string
    {
        return $this->boardName;
    }
}