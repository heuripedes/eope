<?php

/**
 * PreferencesSignals.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

class PreferencesSignals
{
    private $window;
    
    public function __construct ($window)
    {
        $this->window = $window;
    }
    
    public function on_btn_cancel_clicked ()
    {
        $this->window->__destruct();
    }
    
    public function on_btn_ok_clicked ()
    {
        $cm = Etk::get_config();
        $w = $this->window;
        
        $cm->set('files.reopen', $w->widget('pref_reopen')->get_active());
        
        $cm->set('editor.font',  $w->widget('pref_font')->get_font_name());
        $cm->set('editor.autoindent', $w->widget('pref_autoindent')->get_active());
        $cm->set('editor.smart_keys', $w->widget('pref_smart_keys')->get_active());
        $cm->set('editor.tab_style', $w->widget('pref_tab_style')->get_active());
        $cm->set('editor.line_end', $w->widget('pref_line_end')->get_active());
        $cm->set('editor.highlight_line', $w->widget('pref_highlight_line')->get_active());
        $cm->set('editor.match_brackets', $w->widget('pref_match_brackets')->get_active());
        
        Etk::get_app()->document_manager->refresh_all_options();
        $this->window->__destruct();
    }
}
