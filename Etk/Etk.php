<?php

/**
 * Etk.php
 * 
 * This file is part of Etk
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

/*
 * First, we must check if the environment is ok and set some runtime
 * stuff.
 */

if (version_compare(PHP_VERSION, '5.2.5', '<'))
{
    die("[EtkException] Incompatible PHP version. " .
        "Upgrade to 5.2.5 or higher and try again.\n");
}

if (!class_exists('gtk') && !in_array('php-gtk', get_loaded_extensions()))
{
    throw new EtkException('Gtk extension is not loaded');
}

define('HAS_MBSTRING',    function_exists('mb_list_encodings'));
define('HAS_SCINTILLA',   class_exists('GtkScintilla'));
define('HAS_SOURCEVIEW',  class_exists('GtkSourceTagStyle'));
define('HAS_SOURCEVIEW2', class_exists('GtkSourceLanguageManager'));
define('HAS_GETTEXT',     function_exists('gettext'));

define('ETK_VERSION', '0.0.6');

function etk_require_version ($version)
{
	if (!version_compare(ETK_VERSION, $version, '>='))
	{
		throw new EtkException(_('This application requires Etk version %s or higher.'), $version);
	}
}

if (!HAS_GETTEXT)
{
    function bindtextdomain ($domain, $dir) { }    
    function textdomain ($domain) {    }
    function gettext ($msgid) {    return $msgid; }
    function ngettext ($msgid1, $msgid2, $count) { return ($count>1?_($msgid1):_($msgid2)); }
    function _ ($msgid) { return $msgid; }
    function _N ($msgid1, $msgid2, $count) { return ($count>1?_($msgid1):_($msgid2)); }
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
    
    private function __construct ()
    {
    }
    
    public static function get_app ()
    {
        return self::$app;
    }
    
    public static function get_config ()
    {
        return self::$app->get_conf();
    }
    
    // use it if you wish you application class to be "global"
    public static function set_app (EtkApplication $application)
    {
        self::$app = $application;
    }
    
    public static function run ($application, $params = array())
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
        self::set_app(new $application($params));
        self::get_app()->run();
    }
}


class EtkException extends Exception
{
    public function __construct ()
    {
        $args = func_get_args();
        $message = call_user_func_array('sprintf', $args);
        $message = sprintf(_('Etk Exception: %s'), $message);
        parent::__construct($message."\n");
        return;
    }
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
