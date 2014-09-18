<?php
$client = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_DEALER);

$identity = sprintf('%04X', rand(0, 0x10000));
var_dump($identity);
$client->setSockOpt(ZMQ::SOCKOPT_IDENTITY, $identity);
$client->connect('tcp://127.0.0.1:7001');

$frames = [''];
$frames[] = 'Hello World';

$client->sendMulti($frames);

$msg = $client->recvMulti();

var_dump($msg);

