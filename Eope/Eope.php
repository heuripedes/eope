<?php

/**
 * Eope.php
 * 
 * The main Eope class.
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

require_once(ETK_DIR . 'Etk.php');
require_once(ETK_DIR . 'Application.php');
require_once(ETK_DIR . 'Dialog.php');

require_once(APP_DIR . 'ConfigManager.php');
require_once(APP_DIR . 'PluginManager.php');
require_once(APP_DIR . 'PanelManager.php');
require_once(APP_DIR . 'DocumentManager.php');
require_once(APP_DIR . 'EopeSignals.php');
require_once(APP_DIR . 'PluginList.php');

class Eope extends EtkApplication
{
	public $langlist = array();
    
    public $document_manager = null;
    public $sidepanel_manager = null;
    public $bottompanel_manager = null;
    
	public function __construct ()
	{
		parent::__construct(APP_DIR . 'eope.glade', 'main_window');
		
		
		$config = ConfigManager::get_instance();
        $config->load();
        
        $plugin_manager = PluginManager::get_instance();
        
        $this->document_manager = new DocumentManager();
        $this->widget('editor_vbox')->pack_start($this->document_manager);
        $this->widget('editor_vbox')->show_all();
        
        if ((bool)$config->get('files.reopen'))
		{
			$files = explode(':', $config->get('files.last_files'));
			if ($files[0] != '')
			{
				foreach($files as $file)
				{
					$this->document_manager->open_document(urldecode($file));
				}
			}
		}
		
		$this->sidepanel_manager = new PanelManager();
        $this->widget('side_panel')->pack_start($this->sidepanel_manager);
        $this->widget('side_panel')->show_all();
        
        $this->bottompanel_manager = new PanelManager();
        $this->widget('bottom_panel')->pack_start($this->bottompanel_manager);
        $this->widget('bottom_panel')->show_all();

        $this->widget('tab_combo')->set_active($config->get('editor.tab_style'));
        
        $this->resize((int)$config->get('ui.width'), (int)$config->get('ui.height'));
        
        $this->widget('side_panel')->set_visible((bool)$config->get('side_panel.visible'));
       	$this->widget('bottom_panel')->set_visible((bool)$config->get('bottom_panel.visible'));
       	
       	$this->populate_lang_list();
		$this->activate_widgets();
	}
	
	public function run ()
	{
		PluginManager::get_instance()->load_plugins();
		PluginManager::get_instance()->run_event('main_window_create', $this);
		
		$this->connect_glade_to(new EopeSignals());
		
		$this->refresh();
		parent::run();
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
	
	public function get_lang_index ($lang = 'none')
    {
    	if ($lang == '')
    	{
    		$lang = 'none';
		}
    	$lang = strtolower($lang);
    	return array_search($lang, $this->langlist);
	}
	
	public function populate_lang_list ()
	{
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
	}
	
	public function terminate ()
	{

		$config = ConfigManager::get_instance();
		
		$files = array_map('urlencode', $this->document_manager->get_open_files());
		$files = implode(':', $files);
		$config->set('files.last_files', $files);
		
		$size = $this->get_size();
    	$config->set('ui.width', $size[0]);
    	$config->set('ui.height', $size[1]);
    	$config->set('side_panel.visible', $this->sidepanel_manager->is_visible());
    	$config->set('bottom_panel.visible', $this->bottompanel_manager->is_visible());
    	
		$config->store();
		
		PluginManager::get_instance()->run_event('application_terminate');
		
		parent::terminate();
		
		//$files = $this->mainwindow->document_manager->get_modified_files();
	}
}

