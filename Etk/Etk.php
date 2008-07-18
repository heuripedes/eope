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

class Etk extends _Etk
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

class EtkObject
{
    protected $application = null;

    public function get_application ()
    {
        return $this->application;
    }
}
