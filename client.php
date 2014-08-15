<?php
$client = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_REQ, 'Mysock1');

$client->connect('tcp://127.0.0.1:7001');

$client->send('Hello World');

$msg = $client->recv();

var_dump($msg);