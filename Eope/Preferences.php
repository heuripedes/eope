<?php

/**
 * Preferences.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

require_once(APP_DIR . 'PreferencesSignals.php');

class Preferences extends EtkWindow
{
    public function __construct ()
    {
        $cm = ConfigManager::get_instance();
        
        parent::__construct(APP_DIR . 'Glade/preferences.glade','window');
        $this->connect_glade_to(new PreferencesSignals($this));
        $this->show();
        
        $this->widget('pref_reopen')->set_active($cm->get('files.reopen'));
        
        $this->widget('pref_font')->set_font_name($cm->get('editor.font'));
        $this->widget('pref_autoindent')->set_active($cm->get('editor.autoindent'));
        
        $this->widget('pref_smart_keys')->set_active($cm->get('editor.smart_keys'));
        $this->widget('pref_tab_style')->set_active($cm->get('editor.tab_style'));
        $this->widget('pref_line_end')->set_active($cm->get('editor.line_end'));
        
        $this->widget('pref_highlight_line')->set_active($cm->get('editor.highlight_line'));
        $this->widget('pref_match_brackets')->set_active($cm->get('editor.match_brackets'));
        
        $model = Etk::get_app()->widget('encoding_combo')->get_model();//new GtkListStore(GObject::TYPE_STRING);
        $this->widget('pref_def_encoding')->set_model($model);
        return;
        if (HAS_MBSTRING)
        {
            $encs = Etk::get_app()->valid_encodings;
            print_r($encs);
            foreach ($encs as $enc)
            {
                $model->append(array($enc));
            }
        }
        else
        {
            $model->append(array(ini_get('php-gtk.codepage')));
        }

        $this->widget('pref_def_encoding')->show_all();
    }
}
