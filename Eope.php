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
require_once('ConfigurationManager.php');
require_once('PluginManager.php');
require_once('DocumentManager.php');


class Eope extends EtkApplication
{
    public $documents = array();
    public $doc_manager = null;
    public $config = null;
    protected $langlist = array();
    
    public function __construct ()
    {
        $config = new ConfigurationManager();
        $config->load();
        
        parent::__construct();
    	
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
        	 $this->window->widget('lang_combo')->append_text($lang);
		}
		
		$this->window->widget('lang_combo')->set_active(count($this->langlist) -1);
		$this->langlist = array_map('strtolower', $this->langlist);
		
		if ((bool)$config->get('files.reopen'))
		{
			$files = explode(':', $config->get('files.last_files'));
			if ($files[0] != '')
			{
				foreach($files as $file)
				{
					$this->window->document_manager->open_document($file);
				}
			}
		}
		
        $this->run();
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
	
	public function terminate ()
	{
		$config = new ConfigurationManager();
		$files = implode(':', $this->window->document_manager->get_open_files());
		$config->set('files.last_files', $files);
		$config->store();
		parent::terminate();
		//$files = $this->mainwindow->document_manager->get_modified_files();
	}
}

