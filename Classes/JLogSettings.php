<?php

class JLogSettings
{

	const MYSQL_STORAGE = 1;
	const FILE_STORAGE = 2;
	
	public static $WriteImmediately = true;
	public static $StorageMethod = JLogSettings::MYSQL_STORAGE;

	public static $DBInfo = array(
		'driver' => 'mysql',
		'host' => 'localhost',
		'database' => 'JLog',
		'username' => 'example',
		'password' => 'password',
		'tablePrefix' => 'JLog_',
	);

	public static $FileInfo = array(

	);
}

?>