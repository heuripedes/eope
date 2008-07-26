<?php

require_once(ETK_DIR . 'Window.php');

abstract class EtkApplication extends EtkWindow
{
    public function __construct ($gladefile = '', $widgetname = '')
    {
    	parent::__construct ($gladefile, $widgetname);
    }

    public function run ()
    {
        Gtk::main();
    }

    public function terminate ()
    {
        Gtk::main_quit();
        exit;
    }
	
    public function get_application ()
    {
        return $this;
    }
}
