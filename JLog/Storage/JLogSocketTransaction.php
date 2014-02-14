<?php
/**
 * @file Classes/JLogSocketTransaction.php
 * @brief Implemention of the JLogSocketTransaction class.
 */

/**
 * @class JLogSocketTransaction
 * @brief A transaction for logging message directly to a socket.
 */
class JLogSocketTransaction extends JLogTransaction
{
    // the host of the listening server
    private $_host;
    // the open port we connect on
    private $_port;
    // the socket object
    private $_socket;
    // a pointer to the last message we logged
    private $_writePtr;

    /**
     * Constructor for the class.
     * @param array $details The details needed to setup the socket.
     * @throws JLogException Throws an exception if the details were bogus.
     */
    public function __construct($details)
    {
        extract($details, EXTR_OVERWRITE);
        if (!isset($host) || !isset($port)) {
            throw new JLogException('Invalid settings for socket logging.');
        }

        parent::__construct('socket');
        // setup the local variables
        $this->_host = $host;
        $this->_port = intval($port);
        $this->_writePtr = 0;
    }

    /**
     * Writes the unlogged objects to the socket.
     * @param bool $final If true, we will close the socket after writing the
     * remaining logged objects.
     * @return void
     * @throws JLogException Throws an exception if something went wrong
     */
    public function write($final = false)
    {
        // initialize the socket if it's not ready yet
        try {
            if (!isset($this->_socket)) {
                $this->_initializeSocket();
            }
        } catch (JLogException $e) {
            throw $e;
        }

        // count the objects in the log
        $logCount = count($this->log);
        // loop until the write pointer hits the end of the log
        while ($this->_writePtr < $logCount) {
            // grab the object from the log and get its JSON representation
            $toWrite = $this->log[$this->_writePtr]->__toString()."\n";
            // write it into the socket and if we fail throw an exception
            if (false === @fwrite($this->_socket, $toWrite, strlen($toWrite))) {
                fclose($this->_socket);
                throw new JLogException(
                    'Unable to write to socket'
                );
            }
            // increment the write pointer
            $this->_writePtr++;
        }

        // if this if the final log, then we close the socket
        if ($final) {
            fclose($this->_socket);
        }
    }

    // opens a socket with the parameters passed into the constructor
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