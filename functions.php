<?php

if( !function_exists('write_log') ) {

    // 记录日志
    function write_log($message) {
        $file = ROOT_PATH . 'storage\\logs\\' . date('Ymd');

        if( !is_dir($file) ) {
            mkdir($file);
        }

        file_put_contents($file . '\\info.log', $message, FILE_APPEND | LOCK_EX);

    }
}
