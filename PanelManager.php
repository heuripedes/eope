<?php

class PanelManager extends GtkNotebook
{
	protected $panels = array();
	
	public function __construct (Eope $application, MainWindow $window)
    {
        parent::__construct();
        $this->application = $application;
        $this->mainwindow = $window;
	}
	
	public function add_panel (GtkWidget $child, $label = '')
	{
		$this->panels[] = $child;
		
		if ($label == '')
		{
			$label = 'Page '.count($this->panels);
		}
		return $this->append_page($child, new GtkLabel($label));
	}
	
	public function remove_panel ($index)
	{
		$this->remove_page($index);
		unset($this->panels[$index]);
	}
}
