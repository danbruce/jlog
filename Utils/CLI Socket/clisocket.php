<?php

if ($argc != 2) {
    die('Usage: php clisocket.php [port]'."\n");
}
$port = intval($argv[1]);
if ($port <= 1024) {
    die('Invalid port: '.$argv[1]."\n");
}

echo 'Opening socket for listening on port '.$port."\n";
$sock = @stream_socket_server(
    'tcp://0.0.0.0:'.$port,
    $errno, $errstr
);
if (false === $sock) {
    die($errstr.' ('.$errno.')'."\n");
} else {
    while ($conn = stream_socket_accept($sock, -1)) {
        echo 'Connection received.'."\n";
        while (!feof($conn)) {
            echo fread($conn, 1024);
        }
        fclose($conn);
        echo 'Connection closed.'."\n";
    }
    fclose($sock);
}

?>