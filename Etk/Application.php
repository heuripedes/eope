<?php

require_once('Etk/Window.php');

abstract class EtkApplication extends EtkObject
{
	protected $title = '';
	protected $directory = '';
	protected $config = null;
	
    public function __construct ($directory = '.')
    {
    	$this->directory = $directory;
    	
        $this->window = new MainWindow($this);        
        
    }

    public function run ()
    {
        Gtk::main();
    }

    public function terminate ()
    {
        Gtk::main_quit();
    }
	
    public function get_application ()
    {
        return $this;
    }
}
