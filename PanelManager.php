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
	
	protected function update ()
	{
		$this->set_show_tabs($this->get_n_pages() > 1);
		
	}
	
	public function set_visible ($visible, $all = true)
	{
		echo "visible = $visible\n";
		$this->update();
		if ($this->get_n_pages() < 1)
		{
			$this->set_visible(false);
		}
		else
		{
			parent::set_visible($visible, $all);
		}
	}
	
	public function add_panel (GtkWidget $child, $label = '')
	{
		$this->panels[] = $child;
		
		if ($label == '')
		{
			$label = 'Page '.count($this->panels);
		}
		$this->update();
		$page = $this->append_page($child, new GtkLabel($label));
		$this->show_all();
		return $page;
	}
	
	public function remove_panel ($index)
	{
		if ($index instanceof GtkWidget)
		{
			$index = $this->page_num($index);
		}
		$this->remove_page($index);
		unset($this->panels[$index]);
		$this->update();
	}
}
