<?php

require_once('MainWindowSignals.php');

class MainWindow extends EtkWindow
{
    public $notebook = null;
    public $treeview = null;
    public $dir_tree = null;
    public $config = null;

    public $document_manager = null;
    public $sidepanel_manager = null;
    public $bottompanel_manager = null;

    public function __construct (Eope $application)
    {
        parent::__construct($application);

        $config = ConfigManager::get_instance();

        $this->create_from_glade(EOPE_ROOT . '/eope.glade','main_window');

        $this->auto_signal_handler(__CLASS__);

        $this->document_manager = new DocumentManager($application, $this);
        $this->widget('editor_vbox')->pack_start($this->document_manager);
        $this->widget('editor_vbox')->show_all();
		
		$this->sidepanel_manager = new PanelManager($application, $this);
        $this->widget('side_panel')->pack_start($this->sidepanel_manager);
        $this->widget('side_panel')->show_all();
        
        $this->bottompanel_manager = new PanelManager($application, $this);
        $this->widget('bottom_panel')->pack_start($this->bottompanel_manager);
        $this->widget('bottom_panel')->show_all();

        $this->widget('tab_combo')->set_active(6);
        
        $this->resize((int)$config->get('ui.width'), (int)$config->get('ui.height'));
        
		$this->auto_connect();
        $this->refresh();
        $this->activate_widgets();
        
        $this->widget('side_panel')->set_visible((bool)$config->get('side_panel.visible'));
       	$this->widget('bottom_panel')->set_visible((bool)$config->get('bottom_panel.visible'));
       	
       	PluginManager::get_instance()->run_event('main_window_create', array($this));
    }

    public function auto_connect ()
    {
    	parent::auto_connect();
	}
	
	public function activate_widgets ($active = false)
	{
		$this->widget('fake_status_bar')->set_visible($active);
		$this->widget('file_menu_save')->set_sensitive($active);
		$this->widget('file_menu_save_all')->set_sensitive($active);
		$this->widget('file_menu_save_as')->set_sensitive($active);
		$this->widget('file_menu_close')->set_sensitive($active);
		$this->widget('menu_tools_pastebin')->set_sensitive($active);		
		$this->widget('edit_menu')->set_sensitive($active);
	}
}

