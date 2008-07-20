<?php

require_once('MainWindowSignals.php');

class MainWindow extends EtkWindow
{
    public $notebook = null;
    public $treeview = null;
    public $dir_tree = null;
    public $config = null;

    public $document_manager = null;

    public function __construct (EtkApplication $application)
    {
        parent::__construct($application);

        $config = new ConfigurationManager();

        $this->create_from_glade(EOPE_ROOT . '/eope.glade','main_window');

        $this->auto_signal_handler(__CLASS__);

        $this->document_manager = new DocumentManager($application, $this);
        $this->widget('main_hpaned')->add2($this->document_manager);
        //$this->widget('main_hpaned')->reorder_child($this->document_manager, 1);
        $this->widget('main_hpaned')->show_all();

        $this->widget('tab_combo')->set_active(6);
        
        
        $this->resize($config->get('ui.width'), $config->get('ui.height'));
        print($config->get('ui.width'));
       	$this->widget('vbox3')->set_visible((bool)$config->get('ui.directory_view'));
		
		$this->auto_connect();
        $this->refresh();
        $this->activate_widgets();
    }

    public function auto_connect ()
    {
    	parent::auto_connect();
	}
	
	public function activate_widgets ($active = false)
	{
		$this->widget('bottom_box')->set_visible($active);
		$this->widget('file_menu_save')->set_sensitive($active);
		$this->widget('file_menu_save_all')->set_sensitive($active);
		$this->widget('file_menu_save_as')->set_sensitive($active);
		$this->widget('file_menu_close')->set_sensitive($active);
		$this->widget('menu_tools_pastebin')->set_sensitive($active);		
		$this->widget('edit_menu')->set_sensitive($active);
	}
}

