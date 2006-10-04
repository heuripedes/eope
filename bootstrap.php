<?php

define('EOPE_VERSION', '0.0.1');

for($i = 0; $i<$_SERVER['argc']; $i++)
{
    $arg = $_SERVER['argv'][$i];

    if (strtolower($arg) == '--help' || strtolower($arg) == '-h')
    {
        echo "\n";
        echo "Usage: {$_SERVER['argv'][0]} <options> [filename]\n\n";
        echo "  Short options:\n";
        echo "    -c <conffile>   Use  <conffile> as configuration file.\n";
        echo "    -f              Open [filename]\n";
        echo "    -v              Show version information\n";
        echo "    -h              Show help message\n";
        echo "\n";
        echo "  Long options:\n";
        echo "    --c <conffile>  Use  <conffile> as configuration file.\n";
        echo "    --file          Open [filename]\n";
        echo "    --help          Show help message\n";
        echo "    --version       Show version information\n";
        echo "\n";
        exit;
    }

    if (strtolower($arg) == '--version' || strtolower($arg) == '-v')
    {
        echo "\nEOPE  Copyright (C) 2008  Higor Euripedes - PHP ".phpversion()."\n\n";
        echo "This program comes with ABSOLUTELY NO WARRANTY; for details type `show w'.\n";
        echo "This is free software, and you are welcome to redistribute it\n";
        echo "under certain conditions; type `show c' for details.\n";
        exit;
    }
}

error_reporting(E_ALL | E_STRICT);

define('EOPE_ROOT', dirname(__FILE__));

set_include_path('.' . PATH_SEPARATOR . EOPE_ROOT . PATH_SEPARATOR);

if (!class_exists('gtk'))
{
    echo "CANNOT START EOPE: GTK NOT FOUND.\n";
    exit;
}

if (!class_exists('GtkSourceView'))
{
    echo "CANNOT START EOPE: GTKSOURCEVIEW NOT FOUND.\n";
}

require_once('Eope.php');

new Eope();


