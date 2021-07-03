<?php

// Load Opencart config
require_once(dirname(__DIR__) . '/config.php');

require_once dirname(__DIR__) . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Service\Email;

$connection = new AMQPStreamConnection(RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USER, RABBITMQ_PASS);
$channel = $connection->channel();

$channel->queue_declare('email', false, true, false, false);

$callback = function ($msg) {
    $data = json_decode($msg->body, true);

    // You have to choose your config implementation
    [$host, $port, $secure, $login, $password] = $data['setting'];
    [$from, $to, $subject, $body, $attachments] = $data['data'];

    $emailService = new Email($host, $port, $secure, $login, $password);
    $emailService->send($from, $to, $subject, $body, $attachments);

    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('email', '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();