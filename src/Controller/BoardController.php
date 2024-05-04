<?php

namespace App\Controller;

use App\DTO\BoardCreationRequest;
use App\DTO\BoardCreationResponse;
use App\Service\BoardService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: "/board", name: "board_")]
class BoardController
{
    public function __construct(private BoardService $boardService,
                                private RequestStack $requestStack)
    { }

    #[Route(path: "/create", name: "create", methods: ["POST"])]
    public function create(SerializerInterface $serializer): JsonResponse
    {
        $req = new BoardCreationRequest($this->requestStack);
        $data = $this->boardService->createBoard($req->getBoardName());
        $res = new BoardCreationResponse($data, 'Board created');
        $jsonContent = $serializer->serialize($res, 'json');
        return new JsonResponse($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json; charset=utf-8'], true);

    }

}