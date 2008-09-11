<?php

class EtkStatusBar extends GtkFrame
{
	private $hbox;
	
	public function __construct ()
	{
		parent::__construct();
		$this->set_size_request(-1, 25);
		$this->hbox = new GtkHBox(false, 3);
		$this->add($this->hbox);
		$this->set_shadow_type(Gtk::SHADOW_IN);
		//$this->set_border_width(1);
	}
	
	public function __call ($name, $args)
	{
		if (!method_exists($this, $name))
		{
			return call_user_func_array(array($this->hbox, $name), $args);
		}
		
		return call_user_method_array(array($this, $name), $args);
	}
}
