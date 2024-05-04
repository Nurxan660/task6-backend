<?php

namespace App\WebSocket;

use Predis\Client;
use Workerman\Connection\TcpConnection;

class WebSocketHandler
{
    private array $boards = [];

    public function __construct(private Client $redis)
    {
    }

    public function onWebSocketConnect($connection, $http_header): void
    {
        if ($this->isValidConnection($http_header, $connection)) {
            $this->handleValidConnection($http_header, $connection);
        } else {
            $this->rejectInvalidConnection($connection);
        }
    }

    private function isValidConnection($http_header, $connection): bool
    {
        return preg_match("/GET \/(.*?) HTTP/", $http_header) && $connection;
    }

    private function handleValidConnection($http_header, $connection): void
    {
        $boardId = $this->extractBoardId($http_header);
        $this->setupConnection($boardId, $connection);
    }

    private function extractBoardId($http_header): string
    {
        $matches = [];
        preg_match("/GET \/(.*?) HTTP/", $http_header, $matches);
        return $matches[1];
    }

    private function setupConnection(string $boardId, $connection): void
    {
        $connection->id = uniqid();
        $connection->boardId = $boardId;

        $this->sendCachedLines($boardId, $connection);

        $this->addConnectionToBoard($boardId, $connection);
        echo "Connection established on board {$boardId}\n";
    }

    private function sendCachedLines(string $boardId, $connection): void
    {
        $lines = $this->redis->lrange($boardId, 0, -1);
        foreach ($lines as $line) {
            $connection->send($line);
        }
    }

    private function addConnectionToBoard(string $boardId, $connection): void
    {
        if (!isset($this->boards[$boardId])) {
            $this->boards[$boardId] = [];
        }
        $this->boards[$boardId][$connection->id] = $connection;
    }

    private function rejectInvalidConnection($connection): void
    {
        echo "No boardId provided, connection rejected\n";
        $connection->close();
    }

    public function onConnect(TcpConnection $connection): void
    {
        echo "New connection: {$connection->id}\n";
    }

    public function onMessage(TcpConnection $connection, $data): void
    {
        //$lineData = json_decode($data, true);
        $boardId = $connection->boardId;
        $this->saveMessageToBoard($boardId, $data);
        $this->broadcastMessage($boardId, $connection, $data);
    }

    private function saveMessageToBoard(string $boardId, $data): void
    {
        $this->redis->rpush($boardId, $data);
    }

    private function broadcastMessage(string $boardId, $connection, $data): void
    {
        foreach ($this->boards[$boardId] as $conn) {
            if ($conn !== $connection) {
                $conn->send($data);
            }
        }
    }

    public function onClose(TcpConnection $connection): void
    {
        $boardId = $connection->boardId;
        $this->removeConnectionFromBoard($boardId, $connection);
        $this->cleanupEmptyBoard($boardId);
        echo "Connection closed: {$connection->id}\n";
    }

    private function removeConnectionFromBoard(string $boardId, $connection): void
    {
        unset($this->boards[$boardId][$connection->id]);
    }

    private function cleanupEmptyBoard(string $boardId): void
    {
        if (empty($this->boards[$boardId])) {
            unset($this->boards[$boardId]);
        }
    }
}