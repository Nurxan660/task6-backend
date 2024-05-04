<?php

namespace App\WebSocket;

use Predis\Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Workerman\Worker;
use Symfony\Component\Console\Command\Command;

class StartWebSocketServerCommand extends Command
{
    protected static $defaultName = 'websocket:start';

    public function __construct(private WebSocketHandler $handler)
    { parent::__construct(); }


    protected function configure(): void
    {
        $this->setName('websocket:start')
            ->setDescription('Starts the WebSocket server on port 8080.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $worker = new Worker();
        $worker->onWebSocketConnect = function ($connection, $http_header) {
            $this->handler->onWebSocketConnect($connection, $http_header);
        };
        $worker->onConnect = [$this->handler, 'onConnect'];
        $worker->onMessage = [$this->handler, 'onMessage'];
        $worker->onClose = [$this->handler, 'onClose'];

        Worker::runAll();
        return Command::SUCCESS;
    }
}