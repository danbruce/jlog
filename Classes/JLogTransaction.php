<?php

abstract class JLogTransaction
{
    public $id;
    protected $log;
    public $functioning;

    protected function __construct($id)
    {
        $this->id = $id;
        $this->log = array();
        $this->functioning = true;
    }

    public final function log($obj, $level)
    {
        if (!$this->functioning) return;

        array_push(
            $this->log,
            new JLogMessage($this, $obj, $level)
        );

        if(JLogSettings::$WriteImmediately) {
            $this->write();
        }
    }
    
    public final function flush()
    {
        $this->log = array();
    }

    public abstract function write($final = false);
}

?>