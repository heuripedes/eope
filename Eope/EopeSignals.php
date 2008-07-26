<?php

class EopeSignals
{
	public function __construct ()
	{
	}
	
    public function on_main_window_hide ()
    {
    	Etk::get_app()->on_client_quit();
		Etk::get_app()->terminate();
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
    
    public function on_tab_combo_changed ()
    {
    	$app = Etk::get_app();
    	
    	$document = $app->document_manager->get_document();
    	
    	if ($document === false)
    	{
    		return;
		}
		
		$conf = ConfigManager::get_instance();
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
	
	public function on_menu_tools_preferences_activate ()
	{
		if (file_exists(HOME_DIR . '/.eope/eope.conf'))
		{
			echo "[EopeSignals] Loading configuration from " . HOME_DIR . ".eope/eope.conf\n";
			Etk::get_app()->document_manager->open_document(HOME_DIR . '/.eope/eope.conf');
		}
	}
	
	public function on_menu_tools_plugins_activate ()
	{
		new PluginList();
	}
	
// view menu {
	public function on_view_menu_side_panel_activate ()
    {
        Etk::get_app()->widget('side_panel')->set_visible(!Etk::get_app()->widget('side_panel')->is_visible());
    }
    
    public function on_view_menu_bottom_panel_activate ()
    {
    	Etk::get_app()->widget('bottom_panel')->set_visible(!Etk::get_app()->widget('bottom_panel')->is_visible());
	}

// file menu {
    public function on_file_menu_close_activate ()
    {
        Etk::get_app()->document_manager->close_document();
    }

    public function on_file_menu_open_activate ()
    {
        Etk::get_app()->document_manager->open_document(true);
    }

    public function on_file_menu_new_activate ()
    {
    	Etk::get_app()->document_manager->open_document();
    }

    public function on_file_menu_save_activate ()
    {
        Etk::get_app()->document_manager->save_document();
    }
    
    public function on_file_menu_save_all_activate ()
    {
    	Etk::get_app()->document_manager->save_all();
	}

// edit menu
    public function on_menu_edit_undo_activate ()
    {
        Etk::get_app()->document_manager->get_document()->undo();
    }
    
    public function on_menu_edit_redo_activate ()
    {
        Etk::get_app()->document_manager->get_document()->redo();
    }
    
    public function on_menu_edit_paste_activate ()
    {
    	$document = Etk::get_app()->document_manager->get_document();
    	if ($document instanceof Document)
    	{
    		$document->paste();
		}
	}
	
	public function on_menu_edit_cut_activate ()
    {
    	$document = Etk::get_app()->document_manager->get_document();
    	if ($document instanceof Document)
    	{
    		$document->cut();
		}
	}
	
	public function on_menu_edit_copy_activate ()
    {
    	$document = Etk::get_app()->document_manager->get_document();
    	if ($document instanceof Document)
    	{
    		$document->copy();
		}    	
	}
}
