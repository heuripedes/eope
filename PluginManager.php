<?php

require_once('Plugin.php');

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

	public function load ($name)
	{
		if (!class_exists($name.'Plugin'))
		{
			if (file_exists(EtkOS::get_profile().'/.eope/plugins/'.$name.'.php'))
			{
				require_once(EtkOS::get_profile().'/.eope/plugins/'.$name.'.php');
			}
			elseif (file_exists(EOPE_ROOT.'/Plugins/'.$name.'.php'))
			{
				require_once(EOPE_ROOT.'/Plugins/'.$name.'.php');
			}
			else
			{
				Etk::Error(__CLASS__, 'Cannot load '.$name.' plugin: file not found.');
				return;
			}
			
			if (!class_exists($name.'Plugin'))
			{
				Etk::Error(__CLASS__,'Cannot load '.$name.' plugin: class not found.');
				return;
			}
		}
		
		if (array_key_exists($name, $this->plugins))
		{
			Etk::Warning(__CLASS__, 'Plugin '.$name.' is already loaded.');
			return;
		}
		
		$plugin = $name.'Plugin';
		$this->plugins[$name] = new $plugin();

	}
	
	public function unload ($name)
	{
		if (!array_key_exists($name, $this->plugin))
		{
			Etk::Error(__CLASS__, 'Cannot unload '.$name.' plugin: the plugin is not loaded.');
		}
		$this->plugins[$name]->__destruct();
		unset($this->plugins[$name]);
	}
	
	public function run_event ($event, $args = array())
	{
		
		if (!(bool)$event)
		{
			Etk::Trace(__CLASS__,'No valid event specified');
			return;
		}
		$method = 'on_'.trim($event);
		echo count($this->plugins);
		foreach ($this->plugins as $key => $plugin)
		{
			$events = $plugin->get_handled_events();

			if (in_array($event, $events))
			{
				$plugin->$method($args);
			}
		}
	}
}
