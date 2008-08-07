<?php

/**
 * Eope.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurpedes
 * @copyright  Higor "enygmata" Eurpedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

require_once(ETK_DIR . 'Etk.php');
require_once(ETK_DIR . 'Application.php');
require_once(ETK_DIR . 'Dialog.php');

require_once(APP_DIR . 'functions.php');

require_once(APP_DIR . 'Classes/ConfigManager.php');
require_once(APP_DIR . 'Classes/PluginManager.php');

require_once(APP_DIR . 'Widgets/PanelManager.php');
require_once(APP_DIR . 'Widgets/DocumentManager.php');

require_once(APP_DIR . 'EopeSignals.php');

require_once(APP_DIR . 'PluginPrefs.php');
require_once(APP_DIR . 'Preferences.php');


class Eope extends EtkApplication
{
    public $langlist = array();
    
    public $document_manager = null;
    public $sidepanel_manager = null;
    public $bottompanel_manager = null;
    public $argv = array();
    
    public function __construct ($argv)
    {
        parent::__construct(APP_DIR . 'Glade/main.glade', 'main_window');
        
        $this->argv = $argv;
        
        $config = ConfigManager::get_instance();
        $config->load();
        
        $plugin_manager = PluginManager::get_instance();
        
        $this->document_manager = new DocumentManager();
        $this->widget('editor_vbox')->pack_start($this->document_manager);
        $this->widget('editor_vbox')->show_all();
        
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
        
        $this->connect_simple('delete-event', array($this, 'hide_on_delete'));
        $this->populate_lang_list();
        $this->populate_encoding_list();
        $this->activate_widgets();
        
    }
    
    public function run ()
    {
        $config = ConfigManager::get_instance();
        
        $argv = array_slice($this->argv, 1);
        
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
    
    public function populate_lang_list ()
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
    }
    
    public function populate_encoding_list ()
    {
        $model = new GtkListStore(GObject::TYPE_STRING);
        $this->widget('encoding_combo')->set_model($model);
        
        if (function_exists('mb_list_encodings'))
        {
            $encs = mb_list_encodings();
            foreach ($encs as $enc)
            {
                $model->append(array($enc));
            }
        }
        else
        {
            $model->append(array(ini_get('php-gtk.codepage')));
        }
        $this->widget('encoding_combo')->set_active(0);
        $this->widget('encoding_combo')->show_all();
    }
    
    public function on_client_quit ()
    {
        return;
        $app = Etk::get_app();
        PluginManager::get_instance()->run_event('main_window_destroy');
        $modified = $app->document_manager->get_modified_files();

        if (count($modified) > 0)
        {
            $win = new GtkMessageDialog($app->get_window(),
                Gtk::DIALOG_MODAL,
                Gtk::MESSAGE_QUESTION,
                Gtk::BUTTONS_YES_NO,
                'Do you wish to save the modified files before leave Eope?'
                );
            $win->set_title('Confirmation');
            $win->show_all();
            
            if ($win->run() == Gtk::RESPONSE_YES)
            {
                $app->document_manager->save_all();
            }
            
            $win->destroy();
        }
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

