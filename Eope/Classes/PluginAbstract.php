<?php

/**
 * PluginAbstract.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */


abstract class PluginAbstract
{
    protected $status = true;    
    
    public function __construct ()
    {
    }
    
    public function __destruct ()
    {
    }
    
    public function get_status ()
    {
        return $this->status;
    }
    
    public function set_status ($ok = true)
    {
        $this->status = $ok;
    }
    
    public function on_load ()
    {
    }
    
    public function on_unload ()
    {
    }
    
    public function add_to_panel ($panel, $widget, $title = 'Plugin')
    {
        $app = Etk::get_app();
        
        if ($panel == 'side')
        {
            $app->sidepanel_manager->add_panel($widget, $title);
        }
        else // bottom panel
        {
            $app->bottompanel_manager->add_panel($widget, $title);
        }
    }
    
    public function remove_from_panel ($panel, $widget)
    {
        $app = Etk::get_app();
        
        if ($panel == 'side')
        {
            $app->sidepanel_manager->remove_panel($widget);
        }
        else // bottom panel
        {
            $app->bottompanel_manager->remove_panel($widget);
        }
    }
    
    public function add_to_menu ($menu, $widget, $position, $accelmask = null, $key = null)
    {
        $app = Etk::get_app();
        $menu = $menu.'_menu_menu';
        $menuw = $app->widget($menu);
        
        if (!$menuw instanceof GtkWidget || !$widget instanceof GtkWidget)
        {
            echo "[Plugin] The menu does not exist or the widget given is not valid." .
                "Nothing do be done.\n";
            return;
        }
        
        if ($accelmask !== null && $key !== null)
        {
            $accelgroup = $app->get_accel_group();
            $widget->add_accelerator('activate', $accelgroup, ord($key),
                $accelmask, Gtk::ACCEL_VISIBLE);
        }
        
        
        $menuw->add($widget);
        $menuw->reorder_child($widget, $position);
        $menuw->show_all();
    }
    
    public function remove_from_menu ($menu, $widget)
    {
        $app = Etk::get_app();
        $menu = $menu.'_menu_menu';
        $menuw = $app->widget($menu);
        
        if (!$menuw instanceof GtkWidget || !$widget instanceof GtkWidget || !$widget->parent == $menuw)
        {
            echo "[Plugin] The menu does not exist or the widget given is not valid. " .
                 "Nothing do be done.\n";
            return;
        }
        
        $menuw->remove($widget);
    }
    
    abstract public function get_handled_events ();
}
