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
		$this->_fullMessage = array(
			'transaction' => $this->transaction,
			'level'       => $this->errorLevel,
			'contents'    => $this->contents
		);
	}

	public function __toString()
	{
		return json_encode($this->_fullMessage, JSON_FORCE_OBJECT);
	}
}

?>