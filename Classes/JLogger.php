<?php

class JLogger
{
	private static $_currentTransaction = null;
	private static $_written = false;

	public static function init($options = null)
	{
		try {

			// enforce a singleton on the current transaction
			if (isset(JLogger::$_currentTransaction)) {
				throw new JLogException(
					'JLogger::init called more than once.'
				);
			}
					
			switch (JLogSettings::$StorageMethod) {
				case JLogSettings::DATABASE_STORAGE :
					JLogger::$_currentTransaction 
						= new JLogDatabaseTransaction();
					break;
					
				case JLogSettings::FILE_STORAGE :
					JLogger::$_currentTransaction 
						= new JLogFileTransaction();
					break;
				default :
					throw new JLogException(
						'Invalid storage method specified.'
					);
			}
		} catch (JLogException $e) {
			JLogger::dieWithError($e->getMessage());
		}
	}

	public static function log($obj)
	{
		try {
			JLogger::$_currentTransaction->log($obj);
		} catch (JLogException $e) {
			JLogger::dieWithError($e->getMessage());
		}
	}

	public static function close()
	{
		if(JLogger::$_written) return;

		try {
			JLogger::$_currentTransaction->write();
			JLogger::$_written = true;
		} catch(JLogException $e) {
			JLogger::dieWithError($e->getMessage());
		}
	}

	public static function dieWithError($msg)
	{
		die('Caught exception with message: '.$msg."\n");
		exit();
	}

	public static function generateRandomString($len = 20)
	{
		$ret = '';
		while (strlen($ret) < $len) {
			$ret .= chr((rand() % 90)+33);
		}
		return $ret;
	}
}

?>