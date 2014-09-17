<?php
$client = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_DEALER);

$identity = sprintf('%04X', rand(0, 0x10000));
$client->setSockOpt(ZMQ::SOCKOPT_IDENTITY, $identity);
$client->connect('tcp://127.0.0.1:7001');

$frames = [''];
$frames[] = 'Hello World';
$i = 1;
$count = count($frames);
foreach($frames as $v)
{
    $mode = ($i++ == $count) ? NULL : ZMQ::MODE_SNDMORE;
    $client->send($v, $mode);
}
$client->sendMulti($frames);

$msg = $client->recvMulti();

var_dump($msg);

