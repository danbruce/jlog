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
				case JLogSettings::MYSQL_STORAGE :
					JLogger::$_currentTransaction 
						= new JLogMySQLTransaction();
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

	public static function log($ob, $l = JLogMessage::WARNING)
	{
		try {
			JLogger::$_currentTransaction->log($ob, $l);
		} catch (JLogException $e) {
			JLogger::dieWithError($e->getMessage());
		}
	}

	public static function fatal($ob)
	{
		JLogger::log($ob, JLogMessage::FATAL);
	}

	public static function error($ob)
	{
		JLogger::log($ob, JLogMessage::ERROR);
	}

	public static function warning($ob)
	{
		JLogger::log($ob, JLogMessage::WARNING);
	}

	public static function notice($ob)
	{
		JLogger::log($ob, JLogMessage::NOTICE);
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
}

?>