<?php

/**
 * PluginPrefsSignals.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

class PluginPrefsSignals
{
    protected $window;
    public function __construct ($window)
    {
        $this->window = $window;
    }
    public function on_toggle ($renderer, $row, $store)
    {
        $iter = $store->get_iter($row);
        $store->set($iter, 1, !$store->get_value($iter, 1));
    }
    
    public function on_btn_cancel_clicked ()
    {
        $this->window->destroy();
    }
    
    public function on_btn_apply_clicked ()
    {
        $current = PluginManager::get_instance()->list_plugins();
        
        $store= $this->window->get_store();
        $iter = $store->get_iter(0);
        $i = 0;
        while ($iter !== null)
        {
            $name = $store->get_value($iter, 0);
            $load = $store->get_value($iter, 1);
            
            echo "$name [";
            echo $current[$i]['loaded'] ? 'loaded' : 'unloaded' ;
            echo "] " . ($load?'load!':'unload')."\n";
            
            if ($current[$i]['loaded'] != $load)
            {
                switch($load)
                {
                    case true: PluginManager::get_instance()->load($name); break;
                    case false: PluginManager::get_instance()->unload($name); break;
                }
            }
            
            $iter = $store->iter_next($iter);
            $i++;
        }
        
        $this->window->destroy();
    }
}
