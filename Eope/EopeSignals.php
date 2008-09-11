<?php

/**
 * EopeSignals.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

class EopeSignals
{
    public function __construct ()
    {
    }
    
    public function on_quit_app ()
    {
        PluginManager::get_instance()->notify('main_window_destroy');
        $app = Etk::get_app();
        $modified = $app->editor->get_modified_files();

        if (count($modified) > 0)
        {
            $response = EtkDialog::message('Do you wish to save the modified files before leave Eope?',
                        null, EtkDialog::YESNOCANCEL);
            
            switch($response)
            {
                case Gtk::RESPONSE_CANCEL: return true; // true to continue :)
                // TODO: fix save_all();
                case Gtk::RESPONSE_YES: $app->editor->save_all(); break;
            }
        }
        $app->terminate();
    }
    
    public function on_main_window_delete_event ()
    {
        return $this->on_quit_app();
    }
    
    public function on_lang_combo_changed ()
    {
        $app = Etk::get_app();
        $document = $app->document_manager->get_document();
        
        if ($document === false)
        {
            return;
        }
        
        if ($document->get_language_name() != $app->widget('lang_combo')->get_active_text())
        {
            $document->set_language_by_name($app->widget('lang_combo')->get_active_text());
        }
    }
    
    public function on_encoding_combo_changed ()
    {
        $app = Etk::get_app();
        $doc = $app->get_editor()->get_document();
        
        if ($doc === false)
        {
            return;
        }
        
        if (HAS_MBSTRING && $doc['obj']->get_encoding() != $app->widget('encoding_combo')->get_active_text())
        {
            $doc['obj']->set_encoding($app->widget('encoding_combo')->get_active_text());
            $app->get_editor()->on_doc_ui_update($doc['obj']);
        }
    }
    
    public function on_tab_combo_changed ()
    {
        $app = Etk::get_app();
        
        $document = $app->document_manager->get_document();
        
        if ($document === false)
        {
            return;
        }
        
        $conf = Etk::get_config();
        $n = $app->widget('tab_combo')->get_active();
        
        if ($n > 3)
        {
            $conf->set('editor.indent.spaces', true);
        }
        else
        {
            $conf->set('editor.indent.spaces', false);
        }
        
        $conf->set('editor.tab_style', $n);
        $document->refresh_options();
    }

// tools menu
    public function on_tools_menu_preferences_activate ()
    {
        new Preferences();
    }
    
    public function on_tools_menu_plugins_activate ()
    {
        new PluginPrefs();
    }
    
// view menu {
    public function on_view_menu_side_panel_activate ()
    {
        Etk::get_app()->bottompanel->toggle();
        Etk::get_app()->widget('side_panel')->set_visible(Etk::get_app()->bottompanel->is_visible());
        PluginManager::get_instance()->notify('side_panel_toggle', Etk::get_app()->bottompanel);
    }
    
    public function on_view_menu_bottom_panel_activate ()
    {
        Etk::get_app()->widget('bottom_panel')->toggle();
    }

// file menu {
    public function on_file_menu_quit_activate ()
    {
        $this->on_quit_app();
    }
    public function on_file_menu_close_activate ()
    {
        Etk::get_app()->document_manager->close_document();
    }

    public function on_file_menu_open_activate ()
    {
        $filename = EtkDialog::open_file();
        
        if ($filename === false)
        {
            return;
        }
        
        Etk::get_app()->get_editor()->open_file($filename);
    }

    public function on_file_menu_new_activate ()
    {
        Etk::get_app()->get_editor()->open_file();
    }

    public function on_file_menu_save_activate ()
    {
        Etk::get_app()->get_editor()->save_document();
    }
    
    public function on_file_menu_save_as_activate ()
    {
        Etk::get_app()->document_manager->save_document(true);
    }
    
    public function on_file_menu_save_all_activate ()
    {
        Etk::get_app()->document_manager->save_all();
    }

// edit menu
    public function on_edit_menu_undo_activate ()
    {
        Etk::get_app()->document_manager->get_document()->undo();
    }
    
    public function on_edit_menu_redo_activate ()
    {
        Etk::get_app()->document_manager->get_document()->redo();
    }
    
    public function on_edit_menu_paste_activate ()
    {
        $document = Etk::get_app()->document_manager->get_document();
        if ($document instanceof Document)
        {
            $document->paste();
        }
    }
    
    public function on_edit_menu_cut_activate ()
    {
        $document = Etk::get_app()->document_manager->get_document();
        if ($document instanceof Document)
        {
            $document->cut();
        }
    }
    
    public function on_edit_menu_copy_activate ()
    {
        $document = Etk::get_app()->document_manager->get_document();
        if ($document instanceof Document)
        {
            $document->copy();
        }        
    }

    public function on_search_menu_find_activate ()
    {
        new SearchDialog();
    }
}
