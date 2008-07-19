<?php

require_once('Etk/Window.php');

abstract class EtkApplication extends EtkObject
{
	protected $title = '';
	protected $directory = '';
	
    public function __construct ($title = 'EtkApplication', $directory = '.')
    {
    	$this->set_title($title);
    	$this->directory = $directory;
    	
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
        $this->title = $title;
    }
    
    public function get_title ()
    {
    	return $this->title;
	}

    public function get_application ()
    {
        return $this;
    }
}
