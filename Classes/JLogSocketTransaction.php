<?php

class JLogSocketTransaction extends JLogTransaction
{
    private $_host;
    private $_port;

    private $_socket;
    private $_writePtr;

    public function __construct($details)
    {
        try {
            if (!isset($details) || !is_array($details)) {
                throw new JLogException(
                    'Invalid details for socket transaction'
                );
            }
            if (!isset($details['host'])) {
                throw new JLogException(
                    'Missing host for socket transaction'
                );
            }
            if (!isset($details['port'])) {
                throw new JLogException(
                    'Missing port for socket transaction'
                );
            }
            parent::__construct('socket');
            $this->_host = $details['host'];
            $this->_port = intval($details['port']);
            $this->_writePtr = 0;
        } catch (JLogException $e) {
            throw $e;
        }
    }

    public function write($final = false)
    {
        if (!isset($this->_socket)) {
            $this->_initializeSocket();
        }

        $logCount = count($this->log);
        while ($this->_writePtr < $logCount) {
            $toWrite = $this->log[$this->_writePtr]."\n";
            if (false === @fwrite($this->_socket, $toWrite, strlen($toWrite))) {
                fclose($this->_socket);
                throw new JLogException(
                    'Unable to write to socket'
                );
            }
            $this->_writePtr++;
        }

        if ($final) {
            fclose($this->_socket);
        }
    }

    private function _initializeSocket()
    {
        $this->_socket = @stream_socket_client(
            'tcp://'.$this->_host.':'.$this->_port,
            $errno, $errstr
        );
        if (false === $this->_socket) {
            throw new JLogException(
                'Unable to open socket connection'
            );
        }
    }
}

?>