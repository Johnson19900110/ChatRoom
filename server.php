<?php
    define('ROOT_PATH', __DIR__ . '/');
    require ROOT_PATH . 'config/config.php';
    require ROOT_PATH . 'functions.php';

    class WebSocketServer {
        private $server;
        private $addr = '';
        private $port = '';
        private $users = array();  //保存连接的用户，fd=>nickname的形式保存
        private $lock;


        public function __construct($addr, $port)
        {
            $this->addr = $addr;
            $this->port = $port;
        }

        public function start()
        {
            $this->lock = new swoole_lock(SWOOLE_MUTEX);
            $this->server = new swoole_websocket_server($this->addr, $this->port);
            $this->server->set(array(
                'daemonize' => 0,
                'worker_num' => 4,
                'task_worker_num' => 10,
                'max_request' => 1000,
                'log_file' => ROOT_PATH . 'storage\\logs\\swoole.log'
            ));

            $this->server->on('open', array($this, 'onOpen'));
            $this->server->on('message', array($this, 'onMessage'));
            $this->server->on('task', array($this, 'onTask'));
            $this->server->on('finish', array($this, 'onFinish'));
            $this->server->on('close', array($this, 'onClose'));

            $this->server->start();
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
                    $message = '欢迎' . $data->message . '加入了聊天室';
                    $response = array(
                        'type' => 1,    // 1代表系统消息，2代表用户聊天
                        'message' => $message
                    );
                    break;
                case 'chat':
                case 'CHAT':
                    $message = $data->message;
                    $response = array(
                        'type' => 2,    // 1代表系统消息，2代表用户聊天
                        'username' => $this->users[$frame->fd],
                        'message' => $message
                    );
                    break;
                default:
                    return false;
            }

            $this->server->task($response);
        }

        public function onTask($server, $task_id, $from_id, $message)
        {
            foreach ($this->server->connections as $fd) {
                $this->server->push($fd, json_encode($message));
            }
            $server->finish( 'Task' . $task_id . 'Finished' . PHP_EOL);
        }

        public function onFinish($server, $task_id, $data)
        {
            write_log( $data );
        }

        public function onClose($server, $fd)
        {
            $username = $this->users[$fd];
            // 释放客户端，利用锁进行同步
            $this->lock->lock();
            unset($this->users[$fd]);
            $this->lock->unlock();

            if( !$username ) {
                $response = array(
                    'type' => 1,    // 1代表系统消息，2代表用户聊天
                    'message' => $username . '离开了聊天室'
                );
                $this->server->task($response);
            }


            write_log( $fd . ' disconnected');
        }

    }

    $ws = new WebSocketServer(SERVER_LISTEN_ADDR,SERVER_LISTEN_PORT);
    $ws->start();

