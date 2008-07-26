<?php

/**
 * Etk.php
 * 
 * The main Eope Tool Kit class.
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

/*
 * First, we must check if the environment is ok and set some runtime
 * stuff.
 */

if (!class_exists('gtk') && !in_array('php-gtk', get_loaded_extensions()))
{
	throw new EtkException('Gtk extension is not loaded');
}

if (version_compare(PHP_VERSION, '5.2.5', '<'))
{
	die("[EtkException] Incompatible PHP version. " .
		"Upgrade to 5.2.5 or higher and try again.\n");
}

$env = $_ENV + $_SERVER;
$path = '';

if (stristr(PHP_OS, 'win'))
{
	$env = $_ENV + $_SERVER;
	if (isset($env['USERPROFILE']))
	{
		$path = $env['USERPROFILE'] . DIRECTORY_SEPARATOR;
	}
	elseif (isset($env['HOMEPATH']) && isset($env['HOMEDRIVE']))
	{
		$path = $env['HOMEPATH'] . $env['HOMEDRIVE'] . DIRECTORY_SEPARATOR;
	}
	elseif (isset($env['USERNAME']) && file_exists('C:\Documents and Settings\\' . $env['USERNAME']))
	{
		$path = 'C:\Documents and Settings\\' . $env['USERNAME'] . DIRECTORY_SEPARATOR;
	}
}
else
{
	if (isset($env['HOME']))
	{
		$path = $env['HOME'] . DIRECTORY_SEPARATOR;
	}
	elseif ((stristr(PHP_OS, 'darwin') || stristr(PHP_OS, 'mac')) &&
		isset($env['USER']) && file_exists('/Users/' . $env['USER']))
	{
		$path = '/Users/' . $env['USER'] . DIRECTORY_SEPARATOR;
	}
	elseif (isset($env['USER']) &&
		file_exists('/home/' . $env['USER']))
	{
		$path = '/home/' . $env['USER'] . DIRECTORY_SEPARATOR;
	}
}

if (!file_exists($path))
{
	throw new EtkException('Cannot find the home directory.');
}

if (!defined('HOME_DIR'))
{
	define('HOME_DIR', $path);
}

unset($env, $path);

class Etk
{
	protected static $app;
	
	protected function __construct ()
	{
	}
	
	public static function get_app ()
	{
		return self::$app;
	}
	
	// use it if you wish you application class to be "global"
	public static function set_app (EtkApplication $application)
	{
		self::$app = $application;
	}
	
	public static function run ($application)
	{
		if (!defined('ETK_DIR'))
		{
			throw new EtkException ('ETK_DIR is not defined, cannot continue.');
		}
		
		if (!defined('APP_DIR'))
		{
			throw new EtkException ('APP_DIR is not defined, cannot continue.');
		}
		require_once(APP_DIR . $application . '.php');
		self::set_app(new $application());
		self::get_app()->run();
	}
}


class EtkException extends Exception
{
	public function __construct ($message)
	{
		parent::__construct("[EtkException] $message\n");
		return;
	}
}
