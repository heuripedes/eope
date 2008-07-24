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
		echo "trying $name ";
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
			Etk::Warn(__CLASS__, 'Plugin '.$name.' is already loaded.');
			return;
		}
		
		$plugin = $name.'Plugin';
		$plugin = new $plugin();
		
		if ($plugin->get_status() != false)
		{
			$this->plugins[$name] = $plugin;
		}
		else
		{
			$plugin->__destruct();
		}
		echo "... loaded! \n";
	}
	
	public function unload ($name)
	{
		if (!array_key_exists($name, $this->plugins))
		{
			Etk::Error(__CLASS__, 'Cannot unload '.$name.' plugin: the plugin is not loaded.');
		}
		$this->plugins[$name]->__destruct();
		unset($this->plugins[$name]);
	}
	
	public function run_event ()
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
		$path = EtkOS::get_profile() . '/.eope/';
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
		
		$d = dir(EOPE_ROOT . '/Plugins');
		
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
