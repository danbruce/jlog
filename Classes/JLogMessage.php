<?php

class JLogMessage
{

    const FATAL   = 0;
    const ERROR   = -10;
    const WARNING = -20;
    const NOTICE  = -30;

    public $transaction;
    public $contents;
    public $errorLevel;
    private $_fullMessage;

    public function __construct($t, $c, $l = JLogMessage::WARNING)
    {
        $this->transaction = $t->id;
        $this->contents = $c;
        $this->errorLevel = $l;
        $this->_fullMessage = null;
    }

    private function _constructFullMessage()
    {
        $ret = array(
            'transaction' => $this->transaction,
            'level'       => $this->errorLevel,
            'contents'    => $this->contents,
        );

        if (is_object($ret['contents'])) {
            if (method_exists($ret['contents'], '__toString')) {
                $ret['contents'] = $ret['contents']->__toString();
            } else {
                $ret['contents'] = serialize($ret['contents']);
            }
        }

        return $ret;
    }

    public function __toString()
    {
        if (!isset($this->_fullMessage)) {
            $this->_fullMessage = $this->_constructFullMessage();
        }

        return json_encode($this->_fullMessage);
    }
}

?>