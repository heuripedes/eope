<?php

if (defined('Gtk::TYPE_INVALID') || class_exists('Etk'))
{
    return;
}

if (!class_exists('gtk'))
{
    die("Etk fatal error: Gtk not loaded.\n");
}

class _Etk extends Gtk
{
    const TYPE_INVALID = 0;
    const TYPE_NONE = 4;
    const TYPE_INTERFACE = 8;
    const TYPE_CHAR = 12;
    const TYPE_BOOLEAN = 20;
    const TYPE_LONG = 32;
    const TYPE_ENUM = 48;
    const TYPE_FLAGS = 52;
    const TYPE_DOUBLE = 60;
    const TYPE_STRING = 64;
    const TYPE_POINTER = 68;
    const TYPE_BOXED = 72;
    const TYPE_PARAM = 76;
    const TYPE_OBJECT = 80;
    const TYPE_PHP_VALUE = 137706104;
}

final class Etk extends _Etk
{
    public static function Trace ()
    {
        $args = func_get_args();
        $class = $args[0];
        $args = array_slice($args, 1);
        echo 'Etk::'.$class.': '. implode('', $args) . "\n";
    }

    public static function Error ()
    {
        $args = func_get_args();
        $class = $args[0];
        $args = array_slice($args, 1);
        echo 'Etk::'.$class.' error: '. implode('', $args) . "\n";
    }

    public static function FatalError ()
    {
        $args = func_get_args();
        $class = $args[0];
        $args = array_slice($args, 1);
        echo 'Etk::'.$class.' fatal error: '. implode('', $args) . "\n";
        Gtk::main_quit();
        exit;
    }

    public static function Warn ()
    {
        $args = func_get_args();
        $class = $args[0];
        $args = array_slice($args, 1);
        echo 'Etk::'.$class.' warning: '. implode('', $args) . "\n";
    }
}

abstract class EtkObject
{
    protected $application = null;
    protected $window = null;
    protected $name = '';

    public function get_application ()
    {
        return $this->application;
    }
    
    public function get_window ()
    {
    	return $this->window;
	}
	
	public function set_name ()
	{
		return $this->name;
	}
	
	public function get_name ()
	{
		return ($this->name == '' ? __CLASS__ : $this->name);
	}
}

class EtkOS
{
	// (c) Callicore Library
	public static function get_profile ()
	{
		if (stristr(PHP_OS, 'win'))
		{
			$env = $_ENV + $_SERVER;
			if (isset($env['USERPROFILE']))
			{
				return $env['USERPROFILE'] . DS;
			}
			elseif (isset($env['HOMEPATH']) && isset($env['HOMEDRIVE']))
			{
				return $env['HOMEPATH'] . $env['HOMEDRIVE'] . DS;
			}
			elseif (isset($env['USERNAME']) && file_exists('C:\Documents and Settings\\' . $env['USERNAME']))
			{
				return 'C:\Documents and Settings\\' . $env['USERNAME'] . DS;
			}
			else
			{
				return CC::$dir;
			}
		}
		else
		{
			if (isset($_ENV['HOME']))
			{
				return $_ENV['HOME'] . DS;
			}
			elseif ((stristr(PHP_OS, 'darwin') || stristr(PHP_OS, 'mac')) &&
				isset($_ENV['USER']) && file_exists('/Users/' . $_ENV['USER']))
			{
				return '/Users/' . $_ENV['USER'] . DS;
			}
			elseif (isset($_ENV['USER']) &&
				file_exists('/home/' . $_ENV['USER']))
			{
				return '/home/' . $_ENV['USER'] . DS;
			}
			else
			{
				return CC::$dir;
			}
		}
	}
}
