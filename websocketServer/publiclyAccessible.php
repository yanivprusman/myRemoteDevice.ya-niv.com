<?php
// PubliclyAccessible.php
require __DIR__ . '/vendor/autoload.php';
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\Socket\SecureServer;
use React\Socket\Server;
$options = getopt("", ["port:"]);
$port = $options['port'] ?? 8443;  
class PubliclyAccessible implements MessageComponentInterface {
    private $clients;
    private $devices; 
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->devices = new \SplObjectStorage; 
    }
    public function getDevice(ConnectionInterface $from) {
        foreach ($this->devices as $device){
            if($device->queryParameters['deviceIdentifier']==$from->queryParameters['deviceIdentifier']){
                if($device->queryParameters['passWord']==$from->queryParameters['passWord']){
                    return $device;
                }
            }
        }
        return NULL;
    }
    public function getClient(int $deviceIdentifier) {
        foreach ($this->clients as $client){
            if($client->resourceId=== $deviceIdentifier){
                return $client;
            }
        }
        return NULL;
    }
    public function onOpen(ConnectionInterface $conn) {
        // echo "connecting peer to middle man\n";
        $uri = $conn->httpRequest->getUri();
        parse_str($uri->getQuery(), $queryParameters);
        $conn->queryParameters = $queryParameters;
        if(isset($conn->queryParameters['type'])){
            if($conn->queryParameters['type']=='device'){
                $device = $this->getDevice($conn);
                if(!$device){
                    $this->devices->attach($conn);
                    echo "registered device\n";
                }else{
                    echo "device already registered\n";
                }
            }elseif($conn->queryParameters['type']=='client'){
                $device = $this->getDevice($conn);
                if ($device){
                    $this->clients->attach($conn);
                    echo "found registered device\n";
                }else{
                    echo "did not find registered device\n";
                    return;
                }
            }else{
                echo "type not in (device, client)\n";
                return;
            }
        }else{
            echo "type is null should be in (device, client)\n";
            return;
        }
        echo "peer connected to middle man! (resourceId:{$conn->resourceId} type:{$conn->queryParameters['type']} deviceIdentifier:{$conn->queryParameters['deviceIdentifier']})\n";
    }
    public function onMessage(ConnectionInterface $from, $msg) {
        $test = json_decode($msg, true);
        if (!$test) {
            echo "Received invalid JSON\n";
            return;
        }
        if(isset($from->queryParameters['type'])){
            if($from->queryParameters['type']=='client'){
                $device = $this->getDevice($from);
                if (!$device){
                    $this->onClose($from);
                    return;
                }
                try {
                    $device->send(json_encode([
                        'resourceId'=>$from->resourceId,
                        'msg' => $msg
                    ]));
                }catch (\Exception $e) {
                    error_log("Send error: " . $e->getMessage());
                }
            }elseif($from->queryParameters['type']=='device'){
                $data['resourceId'] = json_decode($msg, true)['msg']['resourceId'];
                echo "received message from device sending to " . $data['resourceId'] . "\n";
                $client = $this->getClient($data['resourceId']);
                $client->send(json_encode([
                    "msg"=>$msg
                ]));
            }
        }
        else{
            echo 'somthing went wrong,$conn->queryParameters[\'type\'] null' . "\n";
            echo "from: \ntoo long\n";
            var_dump($from->queryParameters);
            echo "msg:\n";
            var_dump($msg);
        }
    }
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $this->devices->detach($conn);
        //info message
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}
$loop = \React\EventLoop\Loop::get();
echo "Starting server... on port $port\n";
$socket = new Server(
    "0.0.0.0:$port",
    $loop
);
echo "Base socket created\n";
$secureSocket = new SecureServer($socket, $loop, [
    'local_cert' => '/etc/letsencrypt/live/myRemoteDevice.ya-niv.com/fullchain.pem',
    'local_pk' => '/etc/letsencrypt/live/myRemoteDevice.ya-niv.com/privkey.pem',
    'verify_peer' => false,
    'allow_self_signed' => false,
    'verify_depth' => 5,
    'security_level' => 2,
    'verify_expiry' => true,
    'single_dh_use' => true,
    'honor_cipher_order' => true,
    'ciphers' => 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384'
]);
echo "Secure socket created\n";
$server = new IoServer(
    new HttpServer(
        new WsServer(
            new PubliclyAccessible()
        )
    ),
    $secureSocket,
    $loop 
);
echo "Server ready, starting loop...\n";
$loop->run();