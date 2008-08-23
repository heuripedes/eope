<?php

/**
 * Eope.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

require_once(ETK_DIR . 'Etk.php');
require_once(ETK_DIR . 'Application.php');
require_once(ETK_DIR . 'Conf.php');
require_once(ETK_DIR . 'Dialog.php');

require_once(APP_DIR . 'functions.php');

require_once(APP_DIR . 'Classes/PluginManager.php');

require_once(APP_DIR . 'Widgets/PanelManager.php');
require_once(APP_DIR . 'Widgets/DocumentManager.php');

require_once(APP_DIR . 'EopeSignals.php');

require_once(APP_DIR . 'PluginPrefs.php');
require_once(APP_DIR . 'SearchDialog.php');
require_once(APP_DIR . 'Preferences.php');


class Eope extends EtkApplication
{
    public $langlist = array();
    
    public $document_manager = null;
    public $sidepanel = null;
    public $bottompanel = null;
    public $argv = array();
    
    private $firstrun; 
    
    public $valid_encodings = array(
        'UCS-4','UCS-4BE','UCS-4LE','UCS-2','UCS-2BE',
        'UCS-2LE','UTF-32','UTF-32BE','UTF-32LE','UTF-16','UTF-16BE',
        'UTF-16LE','UTF-8','UTF-7','UTF7-IMAP','ASCII','EUC-JP','SJIS',
        'EUCJP-WIN','SJIS-WIN','CP51932','JIS','ISO-2022-JP','ISO-2022-JP-MS',
        'WINDOWS-1252','ISO-8859-1','ISO-8859-2','ISO-8859-3','ISO-8859-4',
        'ISO-8859-5','ISO-8859-6','ISO-8859-7','ISO-8859-8','ISO-8859-9',
        'ISO-8859-10','ISO-8859-13','ISO-8859-14','ISO-8859-15','ISO-8859-16',
        'EUC-CN','CP936','HZ','EUC-TW','BIG-5','EUC-KR','UHC','ISO-2022-KR',
        'WINDOWS-1251','CP866','KOI8-R','ARMSCII-8'
    );
    
    private static $default_conf = array(
        'eope' => array(
            'plugins' => 'DirectoryView:Pastebin'
            ),
        'ui' => array(
            'width' => 640,
            'height' => 480
            ),
        'editor' => array(
            'font' => 'Monospace 10',
            'autoindent' => true,
            'tab_style' => 6,
            'highlight_line' => true,
            'match_brackets' => true, 
            'smart_keys' => true,
            'word_wrap' => true,
            'line_numbers' => true,
            'line_markers' => true,
            'margin'=>true,
            'line_end' => 0, //0:unix,1:windows,2:mac
            'autodetect_le' => true
            ),
        'files' => array(
            'reopen' => false,
            'last_files' => ''
            ),
        
        'sidepanel' => array(
            'visible' => true,
            'width' => 100
            ),
        'bottompanel' => array(
            'visible' => false,
            'height' => 100
            )
    );
    
    public function __construct ($argv)
    {
        parent::__construct(APP_DIR . 'Glade/main.glade', 'main_window', 'main_window');
        
        $this->argv = $argv;
        $this->firstrun = !file_exists(HOME_DIR . '.eope/eope.conf');
        
        if ($this->firstrun)
        {
            @mkdir(HOME_DIR . '.eope');
            touch(HOME_DIR . '.eope/eope.conf');
        }
        
        $this->config->merge(self::$default_conf)
            ->merge(HOME_DIR . '.eope/eope.conf', true);
        
        
        $config = $this->config;
        
        $plugin_manager = PluginManager::get_instance();
        
        $this->document_manager = new DocumentManager();
        $this->widget('editor_vbox')->pack_start($this->document_manager);
        $this->widget('editor_vbox')->show_all();
        
        $this->sidepanel = new PanelManager();
        $this->widget('side_panel')->pack_start($this->sidepanel);
        $this->widget('side_panel')->show_all();
        
        $this->bottompanel = new PanelManager();
        $this->widget('bottom_panel')->pack_start($this->bottompanel);
        $this->widget('bottom_panel')->show_all();

        $this->widget('tab_combo')->set_active($config->get('editor.tab_style'));
        
        $this->resize((int)$config->get('ui.width'), (int)$config->get('ui.height'));
        
        $this->widget('side_panel')->set_visible((bool)$config->get('sidepanel.visible'));
        $this->widget('bottom_panel')->set_visible((bool)$config->get('bottompanel.visible'));
        
        //$this->connect_simple('delete-event', array($this, 'hide_on_delete'));
        $this->populate_lists();
        $this->activate_widgets();
    }
    
    public function run ()
    {
        $config = $this->config;
        
        $argv = array_slice($this->argv, 1);
        
        if ($this->firstrun)
        {
            $this->document_manager->open_document(APP_DIR . 'Welcome.txt');
        }
        
        if ((bool)$config->get('files.reopen'))
        {
            $files = explode(':', $config->get('files.last_files'));
            if ($files[0] != '')
            {
                foreach($files as $file)
                {
                    echo $file."\n";
                    $this->document_manager->open_document(urldecode($file));
                }
            }
        }
        
        foreach ($argv as $arg)
        {
            $this->document_manager->open_document($arg);
        }
        
        PluginManager::get_instance()->load_plugins();
        PluginManager::get_instance()->notify('main_window_create', $this);
        
        $this->connect_glade_to(new EopeSignals());
        
        $this->refresh();
        
        parent::run();
    }
    
    public function activate_widgets ($active = false)
    {
        PluginManager::get_instance()->notify('activate_widgets', $active);
        $this->widget('fake_status_bar')->set_visible($active);
        $this->widget('file_menu_save')->set_sensitive($active);
        $this->widget('file_menu_save_all')->set_sensitive($active);
        $this->widget('file_menu_save_as')->set_sensitive($active);
        $this->widget('file_menu_close')->set_sensitive($active);
        $this->widget('edit_menu')->set_sensitive($active);
        $this->widget('search_menu')->set_sensitive($active);
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
    
    public function populate_lists ()
    {
        $model = new GtkListStore(GObject::TYPE_STRING);
        $this->widget('lang_combo')->set_model($model);
        
        $lm = new GtkSourceLanguagesManager();
        $lang_objects = $lm->get_available_languages();

        $this->langlist = array();
        
        foreach ($lang_objects as $lang)
        {
            $this->langlist[] = $lang->get_name();
        }
        
        sort($this->langlist);
        
        $this->langlist[] = 'None';
        
        foreach ($this->langlist as $lang)
        {
             $model->append(array($lang));
        }
        
        $this->widget('lang_combo')->set_active(count($this->langlist) -1);
        $this->widget('lang_combo')->show_all();
        $this->langlist = array_map('strtolower', $this->langlist);

        $model = new GtkListStore(GObject::TYPE_STRING);
        $this->widget('encoding_combo')->set_model($model);
        
        if (HAS_MBSTRING)
        {
            $encs = mb_list_encodings();
            foreach ($encs as $enc)
            {
                $enc = strtoupper($enc);
                if (in_array($enc, $this->valid_encodings))
                {
                    $model->append(array($enc));
                }
            }
        }
        else
        {
            $model->append(array(ini_get('php-gtk.codepage')));
        }
    }
    
    public function on_client_quit ()
    {
       
    }
    
    public function terminate ()
    {
        $config = $this->config;
        
        $files = array_map('urlencode', $this->document_manager->get_open_files());
        $files = implode(':', $files);
        
        $size = $this->get_size();
        
        
        $config->set('ui.width', $size[0])
               ->set('ui.height', $size[1])
               ->set('side_panel.visible', $this->sidepanel->is_visible())
               ->set('bottom_panel.visible', $this->bottompanel->is_visible())
               ->set('files.last_files', $files)
               // the line below must be the last one.
               ->store(HOME_DIR . '.eope/eope.conf');
        
        $plugins = PluginManager::get_instance()->list_plugins();
        
        $tmp = '';
        for ($i=0;$i<count($plugins); $i++)
        {
            if ($plugins[$i]['loaded'])
            {
                $tmp .= $plugins[$i]['name']  . ($i < count($plugins)-1 ? ':' : '');
            }
        }
        
        PluginManager::get_instance()->unload_plugins();
        
        parent::terminate();
    }
}

