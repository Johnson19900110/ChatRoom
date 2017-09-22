<?php
    require './config.php';

    class WebSocketServer {
        private $addr = '';
        private $port = '';

        public function __construct($addr, $port)
        {
            $this->addr = $addr;
            $this->port = $port;
        }

        public function start()
        {
            $server = new swoole_websocket_server($this->addr, $this->port);
            $server-set(array(
                'daemonize' => 0,
                'worker_num' => 4,
                'task_worker_num' => 10,
                'max_request' => 1000,

            ));

            $server->on('open', array($this, 'onOpen'));
            $server->on('message', array($this, 'onMessage'));
            $server->on('task', array($this, 'onTask'));
            $server->on('finish', array($this, 'onFinish'));
            $server->on('close', array($this, 'onClose'));

            $server->start();
        }

        public function onOpen($server, $request)
        {
            print_r($request);
        }

        public function onMessage($server, $frame)
        {
            print_r($frame);
            $server->task($frame);
        }

        public function onTask($server, $task_id, $from_id, $frame)
        {
            echo $frame->data . PHP_EOL;
            $server->finish('OK');
        }

        public function onFinish($server, $task_id, $data)
        {
            echo $data . PHP_EOL;
        }

        public function onClose($server, $fd)
        {
            echo $fd . 'closed';
        }

    }

    $ws = new WebSocketServer(SERVER_LISTEN_ADDR,SERVER_LISTEN_PORT);
    $ws->start();

