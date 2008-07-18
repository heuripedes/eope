<?php

require_once('Etk/Window.php');

abstract class EtkApplication extends EtkObject
{
    public $window = null;
    private $glade = null;

    public function __construct ()
    {
        if (!$this->window)
        {
            $this->window = new MainWindow($this);
        }
    }

    public function run ()
    {
        Gtk::main();
    }

    public function terminate ()
    {
        Gtk::main_quit();
    }

    public function set_title ($title)
    {
        $this->window->set_title($title);
    }

    public function get_application ()
    {
        return $this;
    }
}
