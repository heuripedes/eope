<?php

/**
 * PluginManager.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

require_once(APP_DIR . 'Classes/PluginAbstract.php');

class PluginManager
{
    public $plugins = array();
    
    protected static $instance = null;
    
    protected function __construct ()
    {

    }
    
    public static function get_instance ()
    {
        if (self::$instance == null)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function load_plugins ()
    {
        $config = Etk::get_config();
        $plugins = explode(':', $config->get('eope.plugins'));
        
        foreach($plugins as $plugin)
        {
            if (trim($plugin))
            {
                $this->load($plugin);
            }
        }
    }
    public function unload_plugins ()
    {
        foreach (array_keys($this->plugins) as $name)
        {
            $this->unload($name);
        }
    }

    public function load ($name)
    {
        echo "[PluginManager] Loading $name...\n";
        
        if (!class_exists($name.'Plugin'))
        {
            if (file_exists(HOME_DIR.'.eope/plugins/'.$name.'.php'))
            {
                require_once(HOME_DIR.'.eope/plugins/'.$name.'.php');
            }
            elseif (file_exists(APP_DIR.'Plugins/'.$name.'.php'))
            {
                require_once(APP_DIR.'/Plugins/'.$name.'.php');
            }
            else
            {
                throw new EtkException ('Cannot load '.$name.' plugin: file not found.');
            }
            
            if (!class_exists($name.'Plugin'))
            {
                throw new EtkException ('Cannot load '.$name.' plugin: class not found.');
            }
        }
        
        if (array_key_exists($name, $this->plugins))
        {
            echo "[PluginManager] $name is already loaded.\n";
            return;
        }
        
        $plugin = $name.'Plugin';
        $plugin = new $plugin();
        
        if ($plugin->get_status() === true)
        {
            $this->plugins[$name] = $plugin;
            $this->plugins[$name]->on_load();
            return;
        }
        $plugin->__destruct();
        echo "[PluginManager] $name cannot be loaded. (status = false)\n";
    }
    
    public function unload ($name)
    {
        if (!array_key_exists($name, $this->plugins))
        {
            echo "[PluginManager] Cannot unload $name plugin: the plugin is not loaded.\n";
            return;
        }
        $this->plugins[$name]->on_unload();
        $this->plugins[$name]->__destruct();
        unset($this->plugins[$name]);
    }
    
    
    
    public function notify ()
    {
        $args = func_get_args();
        $event = $args[0];
        $args = array_slice($args, 1);
        
        if (!(bool)$event)
        {
            Etk::Trace(__CLASS__,'No valid event specified');
            return;
        }
        $method = 'on_'.trim($event);
        
        foreach ($this->plugins as $key => $plugin)
        {
            $events = $plugin->get_handled_events();

            if (in_array($event, $events))
            {
                call_user_func_array(array($plugin, $method), $args);
            }
        }
    }
    
    public function list_plugins ()
    {
        $path = HOME_DIR . '/.eope/';
        if (file_exists($path) && !file_exists($path.'plugins'))
        {
            mkdir($path.'plugins');
        }
        $plugins = array();
        $d = dir($path.'plugins');
        
        while (false != ($e = $d->read()))
        {
            if (@strtolower(end(explode('.', $e))) == 'php')
            {
                $name = substr($e, 0, strpos($e, '.'));
                $plugins[] = array(
                    'name' => $name,
                    'loaded' => array_key_exists($name, $this->plugins)
                );
            }
        }
        $d->close();
        
        $d = dir(APP_DIR . '/Plugins');
        
        while (false != ($e = $d->read()))
        {
            if (@strtolower(end(explode('.', $e))) == 'php')
            {
                $name = substr($e, 0, strpos($e, '.'));
                $plugins[] = array(
                    'name' => $name,
                    'loaded' => array_key_exists($name, $this->plugins)
                );
            }
        }
        $d->close();

        return $plugins;
    }
}
