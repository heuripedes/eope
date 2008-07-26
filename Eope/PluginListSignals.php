<?php

class PluginListSignals
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
		$current = $this->window->get_list();
		$store= $this->window->get_store();
		$iter = $store->get_iter(0);
		$i = 0;
		while ($iter !== null)
		{
			$name = $store->get_value($iter, 0);
			$load = $store->get_value($iter, 1);
			
			echo "$name [{$current[$i]['loaded']}] == $load\n";
			
			if ($load == true && $current[$i]['loaded'] === false)
			{
				PluginManager::get_instance()->load($name);
			}
			elseif ($load == false && $current[$i]['loaded'] === true)
			{
				PluginManager::get_instance()->unload($name);
			}
			$iter = $store->iter_next($iter);
			$i++;
		}
	}
}
