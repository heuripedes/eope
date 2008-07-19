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
require_once('DocumentManager.php');

class Eope extends EtkApplication
{
    public $documents = array();
    public $doc_manager = null;
    public $config = array();
    protected $langlist = array();
    
    public function __construct ()
    {
    	$this->config = parse_ini_file(EOPE_ROOT . '/eope.conf', true);
        parent::__construct();
        
        $lang_manager = new GtkSourceLanguagesManager();
        $lang_objects = $lang_manager->get_available_languages();
        
        $this->langlist = array();
        
        foreach($lang_objects as $obj)
        {
			$this->langlist[] = $obj->get_name();
        }
        
        sort($this->langlist);
        
        $this->langlist[] = 'None';
        
        foreach($this->langlist as $lang)
        {
        	 $this->window->widget('lang_combo')->append_text($lang);
		}
		
		$this->window->widget('lang_combo')->set_active(count($this->langlist) -1);
		$this->langlist = array_map('strtolower', $this->langlist);
		
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
		parent::terminate();
		//$files = $this->mainwindow->document_manager->get_modified_files();
	}
}

