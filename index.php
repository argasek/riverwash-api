<?php

require_once 'vendor/autoload.php';

date_default_timezone_set('Europe/Warsaw');

use AyeAye\Api\Api;
use Dotenv\Dotenv;
use Riverwash\UsersController;
use Psr\Log\AbstractLogger;

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

class EchoLogger extends AbstractLogger {
    public function log($level, $message, array $context = array()) {
        echo $message . PHP_EOL;
        $this->logArray($context);
    }

    public function logArray($array, $indent = '  ') {
        foreach ($array as $key => $value) {
            if (!is_scalar($value)) {
                echo $indent . $key . ':' . PHP_EOL;
                $this->logArray($value, $indent . '  ');
                continue;
            }
            echo $indent . $key . ': ' . $value;
        }
    }
}

$initialController = new UsersController();
$api = new Api($initialController);
$api->setLogger(new EchoLogger);

$api->go()->respond();

