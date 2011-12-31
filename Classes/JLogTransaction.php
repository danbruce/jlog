<?php

abstract class JLogTransaction
{
	public $id;
	protected $log;
	protected $transactionID;

	public function __construct($id)
	{
		$this->id = $id;
		$this->log = array();
	}

	public final function log($obj, $level)
	{
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

	public abstract function write();
}

?>