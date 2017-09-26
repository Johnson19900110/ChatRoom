<?php
    define('ROOT_PATH', __DIR__ . '/');
    require ROOT_PATH . 'config/config.php';
    require ROOT_PATH . 'functions.php';

    class WebSocketServer {
        private $addr = '';
        private $port = '';
        private $users = array();

        public function __construct($addr, $port)
        {
            $this->addr = $addr;
            $this->port = $port;
        }

        public function start()
        {
            $server = new swoole_websocket_server($this->addr, $this->port);
            $server->set(array(
                'daemonize' => 0,
                'worker_num' => 4,
                'task_worker_num' => 10,
                'max_request' => 1000,
                'log_file' => ROOT_PATH . 'storage\\logs\\swoole.log'
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
            $message = array(
                'remote_addr' => $request->server['remote_addr'],
                'request_time' => date('Y-m-d H:i:s', $request->server['request_time'])
            );
            write_log($message);
        }

        public function onMessage($server, $frame)
        {
            $data = json_decode($frame->data);
            switch ($data->type) {
                case 'init':
                case 'INIT':
                    $this->users[$frame->fd] = $data->message;
                    $frame->response = '欢迎' . $data->message . '加入了直播间';break;
                case 'chat':
                case 'CHAT':
                    $frame->response = $this->users[$frame->fd] . '：' . $data->message;
            }

            $frame->users = $this->users; //此处需要通过$frame传入全局变量，因为在task任务中无法获取全局变量

            $server->task($frame);
        }

        public function onTask($server, $task_id, $from_id, $frame)
        {
            print_r($frame);
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

