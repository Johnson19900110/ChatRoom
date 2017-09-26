<?php

if( !function_exists('write_log') ) {

    // 记录日志
    function write_log($message) {
        $message = date('[ Y-m-d H:i:s ]') . 'local.INFO:' . formatMessage($message) . PHP_EOL;

        $file = ROOT_PATH . 'storage/logs/' . date('Ymd');

        if( !is_dir($file) ) {
            mkdir($file);
        }

        file_put_contents($file . '/info.log', $message, FILE_APPEND | LOCK_EX);

    }
}

if( !function_exists('formatMessage') ) {
    function formatMessage($message) {
        if( is_array($message) ) {
            return var_export($message, true);
        }
        return $message;
    }
}
