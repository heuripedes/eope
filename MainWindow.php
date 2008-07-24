<?php

require_once('MainWindowSignals.php');
require_once('PluginList.php');

class MainWindow extends EtkWindow
{
    public $langlist = array();
    

    public $document_manager = null;
    public $sidepanel_manager = null;
    public $bottompanel_manager = null;
    
    public $statusbar = null;
    public $lang_combo = null;
    public $tab_combo = null;
    public $line_label = null;
    public $column_label = null;

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

        $this->widget('tab_combo')->set_active($config->get('editor.tab_style'));
        
        $this->resize((int)$config->get('ui.width'), (int)$config->get('ui.height'));
        
		$this->auto_connect();
        $this->refresh();
        
        $this->widget('side_panel')->set_visible((bool)$config->get('side_panel.visible'));
       	$this->widget('bottom_panel')->set_visible((bool)$config->get('bottom_panel.visible'));
       	
       	$lang_manager = new GtkSourceLanguagesManager();
        $lang_objects = $lang_manager->get_available_languages();

        $this->langlist = array();
        
        foreach ($lang_objects as $obj)
        {
			$this->langlist[] = $obj->get_name();
        }
        
        sort($this->langlist);
        
        $this->langlist[] = 'None';
        
        foreach ($this->langlist as $lang)
        {
        	 $this->widget('lang_combo')->append_text($lang);
		}
		
		$this->widget('lang_combo')->set_active(count($this->langlist) -1);
		$this->langlist = array_map('strtolower', $this->langlist);
		
		PluginManager::get_instance()->run_event('main_window_create', $this);
		$this->activate_widgets();
    }

    public function auto_connect ()
    {
    	parent::auto_connect();
	}
	
	public function set_statusbar_styles ()
	{
		
		Gtk::rc_parse_string("
			style 'status_widget'
			{
				xthickness = 0
                ythickness = 0
			}
			widget '*fake_status_bar*' style 'status_widget'
		");
		$this->widget('fake_status_bar')->set_name('fake_status_bar');
	}
	
	public function activate_widgets ($active = false)
	{
		PluginManager::get_instance()->run_event('activate_widgets', $active);
		$this->widget('fake_status_bar')->set_visible($active);
		$this->widget('file_menu_save')->set_sensitive($active);
		$this->widget('file_menu_save_all')->set_sensitive($active);
		$this->widget('file_menu_save_as')->set_sensitive($active);
		$this->widget('file_menu_close')->set_sensitive($active);
		$this->widget('edit_menu')->set_sensitive($active);
		
		
	}
}

