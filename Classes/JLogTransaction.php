<?php

abstract class JLogTransaction
{
	public $id;
	protected $log;

	public function __construct($id)
	{
		$this->id = $id;
		$this->log = array();
	}

	public final function log($obj)
	{
		array_push(
			$this->log,
			new JLogMessage($this, $obj)
		);
	}
	
	public final function flush()
	{
		$this->log = array();
	}

	public abstract function write();
}

?>