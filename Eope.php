<?php
/**
 * Eope.php
 *
 * Enygmata's own php editor. - A simple and lightweight php editor.
 *
 * @author Higor "enygmata" Eurípedes
 */

require_once('Etk/Etk.php');
require_once('Etk/Application.php');
require_once('MainWindow.php');
require_once('FileDialogs.php');
require_once('ConfigManager.php');
require_once('PluginManager.php');
require_once('PanelManager.php');
require_once('DocumentManager.php');


class Eope extends EtkApplication
{
    protected $langlist = array();
    protected $plugin_manager ;
    
    public function __construct ()
    {
        $config = ConfigManager::get_instance();
        $config->load();
        $this->plugin_manager = PluginManager::get_instance();
        
        $plugins = explode(':', $config->get('eope.plugins'));
        
        foreach($plugins as $plugin)
        {
        	if (trim($plugin))
        	{
        		$this->plugin_manager->load($plugin);
			}
		}
		
        parent::__construct();
        
        
        
        
        if ((bool)$config->get('files.reopen'))
		{
			$files = explode(':', $config->get('files.last_files'));
			if ($files[0] != '')
			{
				foreach($files as $file)
				{
					$this->window->document_manager->open_document(urldecode($file));
				}
			}
		}
    	
    	$this->plugin_manager->run_event('application_run');
        $this->run();
    }
    
    public function get_lang_index ($lang = 'none')
    {
    	if ($lang == '')
    	{
    		$lang = 'none';
		}
    	$lang = strtolower($lang);
    	return array_search($lang, $this->window->langlist);
	}
	
	public function terminate ()
	{
		$config = ConfigManager::get_instance();
		
		$files = array_map('urlencode', $this->window->document_manager->get_open_files());
		$files = implode(':', $files);
		$config->set('files.last_files', $files);
		$config->store();
		
		$this->plugin_manager->run_event('application_terminate');
		parent::terminate();
		//$files = $this->mainwindow->document_manager->get_modified_files();
	}
}

