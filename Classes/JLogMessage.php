<?php

class JLogMessage
{
	public $transaction;
	public $contents;
	private $_fullMessage;

	public function __construct($t, $c)
	{
		$this->transaction = $t->id;
		$this->contents = $c;
		$this->_fullMessage = array(
			'contents' => $this->contents
		);
	}

	public function __toString()
	{
		return json_encode($this->_fullMessage, JSON_FORCE_OBJECT);
	}
}

?>