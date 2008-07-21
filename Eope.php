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
require_once('ProjTree.php');
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
        
        $this->plugin_manager->load('DirectoryView');
        
        parent::__construct();
    	
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
		$files = implode(':', $this->window->document_manager->get_open_files());
		$config->set('files.last_files', $files);
		$config->store();
		parent::terminate();
		//$files = $this->mainwindow->document_manager->get_modified_files();
	}
}

