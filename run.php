#!/usr/local/bin/php
<?php

/**
 * run.php
 *
 * This file starts your application.
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

error_reporting(E_ALL | E_STRICT);

define('ETK_DIR', '.' . DIRECTORY_SEPARATOR . 'Etk'  . DIRECTORY_SEPARATOR);
define('APP_DIR', '.' . DIRECTORY_SEPARATOR . 'Eope'  . DIRECTORY_SEPARATOR);

if (in_array('--noterm', $_SERVER['argv']))
{
	ob_start();
}


require_once(ETK_DIR . 'Etk.php');
Etk::run('Eope');

if (in_array('--noterm', $_SERVER['argv']))
{
	$fp = fopen(HOME_DIR . '.eope/log.txt', 'w');
	fwrite($fp, ob_get_clean());
	fclose($fp);
}


