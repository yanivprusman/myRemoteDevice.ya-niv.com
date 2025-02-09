<?php
// myremotedevice1.php
require __DIR__ . '/vendor/autoload.php';
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;
use Ratchet\Client;
$options = getopt("", ["port:","deviceIdentifier:","passWord:"]);
$port = $options['port'] ?? 8443; 
$deviceIdentifier = $options['deviceIdentifier'] ?? 'myRemoteDevice1';
$passWord = $options['passWord'] ?? '3iW6xNYuALYa1bD';
Client\connect("wss://myRemoteDevice.ya-niv.com:$port/?type=device&deviceIdentifier=$deviceIdentifier&passWord=$passWord")->then(function($conn) use ($deviceIdentifier) {
    echo "Connected to WebSocket server\n";
    $conn->on('message', function($msg) use ($conn) {
        $test = json_decode($msg, true);
        if (!$test) {
            echo "Received invalid JSON\n";
            return;
        };
        echo "Received message: " . $msg . "\n";
        $data['msg'] = json_decode(json_decode($msg, true)['msg'], true);
        $data['resourceId'] = json_decode($msg, true)['resourceId'];
        if($data['msg']['action']==='getPage'){
            $conn->send(json_encode([
                'msg' => [
                    'resourceId'=>$data['resourceId'],
                    'action' => 'respondingToGetPage',
                    'page' => 'hi from device 1'
                ]
            ]));
        }
    });
}, function ($e) {
    echo "Could not connect: {$e->getMessage()}\n";
});
Loop::run();

