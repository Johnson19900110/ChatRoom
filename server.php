<?php
    require './config.php';

    class WebSocketServer {
        private $addr = '';
        private $port = '';

        public function __construct()
        {
            $this->addr = SERVER_LISTEN_ADDR;
            $this->port = SERVER_LISTEN_PORT;
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
        }

        public function onOpen($server, $request)
        {
            print_r($request);
        }

        public function onMessage()
        {

        }

        public function onTask()
        {

        }

        public function onFinish()
        {

        }

        public function onClose()
        {

        }

    }


