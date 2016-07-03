<?php

require_once 'vendor/autoload.php';

use AyeAye\Api\Api;
use Dotenv\Dotenv;
use Riverwash\UsersController;
use Psr\Log\AbstractLogger;

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

header('Access-Control-Allow-Origin: '. getenv('ACCESS_CONTROL_ALLOW_ORIGIN'));
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: origin, content-type, accept');

date_default_timezone_set('Europe/Warsaw');

if (!getenv('LUNO_KEY') ||
    !getenv('LUNO_SECRET') ||
    !getenv('SLACK_WEBHOOK') ||
    !getenv('SLACK_CHANNEL') ||
    !getenv('SESSION_NAME')) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'data' => [
            'error' => 'Please configure environment variables.'
        ]
    ]);
    exit(0);
}

session_name(getenv('SESSION_NAME'));
session_start();

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

