<?php

class JLogSettings
{

	const DATABASE_STORAGE = 1;
	const FILE_STORAGE = 2;

	public static $StorageMethod = JLogSettings::DATABASE_STORAGE;

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