<?php

/**
 * Application.php
 * 
 * This file is part of Etk
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */
require_once(ETK_DIR . 'Window.php');

abstract class EtkApplication extends EtkWindow
{
    protected $config;
    
    public function __construct ($gladefile = '', $widgetname = '', $root = FALSE)
    {
        parent::__construct ($gladefile, $widgetname);
        
        if (class_exists('EtkConf'))
        {
            $this->config = new EtkConf();
        }
    }
    
    public function get_conf ()
    {
        return $this->config;
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
    
    public function get_name ()
    {
        return 'EtkApplication';
    }
}
