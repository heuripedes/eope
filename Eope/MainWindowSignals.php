<?php

class MainWindowSignals extends EtkSignalHandler
{
    public function on_main_window_destroy ()
    {
    	PluginManager::get_instance()->run_event('main_window_destroy', $this->window);
    	$conf = ConfigManager::get_instance();
    	$size = $this->window->get_size();
    	$conf->set('ui.width', $size[0]);
    	$conf->set('ui.height', $size[1]);
    	$conf->set('side_panel.visible', $this->window->sidepanel_manager->is_visible());
    	$conf->set('bottom_panel.visible', $this->window->bottompanel_manager->is_visible());
        $this->application->terminate();
    }
    
    public function on_lang_combo_changed ()
    {
    	$document = $this->window->document_manager->get_document();
    	
    	if ($document === false)
    	{
    		return;
		}
		
		if ($document->get_language_name() != $this->window->widget('lang_combo')->get_active_text())
		{
			$document->set_language_by_name($this->window->widget('lang_combo')->get_active_text());
		}
    }
    
    public function on_tab_combo_changed ()
    {
    	$document = $this->window->document_manager->get_document();
    	
    	if ($document === false)
    	{
    		return;
		}
		
		$conf = ConfigManager::get_instance();
		$n = $this->window->widget('tab_combo')->get_active();
		
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
		$userdir = EtkOS::get_profile();

		if (file_exists($userdir . '/.eope/eope.conf'))
		{
			Etk::Trace(__CLASS__, 'Loading configuration from '.$userdir . '/.eope/eope.conf');
			$this->window->document_manager->open_document($userdir . '/.eope/eope.conf');
		}
	}
	
	public function on_menu_tools_plugins_activate ()
	{
		new PluginList($this->application);
	}
	
    public function on_menu_tools_php_syntax_activate ()
    {
        $path = $this->application->doc_manager->get_current_document_path();

        if ($path)
        {
            $cmd = realpath('../php-gtk2/php.exe').' -l -c "'.realpath('../php-gtk2/phpgtk.ini').'" -f "'.$path.'"';
            echo $cmd;
            exec($cmd, $output);
            $output = implode("\n", $output);
            $buff = $this->window->widget('console_output')->get_buffer();
            $output = $buff->get_text($buff->get_start_iter(), $buff->get_end_iter()) ."\n". $output;
            $buff->set_text($output);
            $this->window->refresh();
        }
    }

// tree buttons {
    public function on_btn_tree_refresh_clicked ()
    {
        $treeview = $this->window->widget('treeview');

        $treeview->set_cursor_on_cell(array(0));

        $selection = $this->window->widget('treeview')->get_selection();

        list($model, $iter) = $selection->get_selected();

        if (!$model instanceof GtkTreeStore || !$iter instanceof GtkTreeIter)
        {
            Etk::Trace(__CLASS__, 'Nothing selected');
            return;
        }

        //$ptree = new ProjTree($this->window->widget('treeview'));
        //$ptree->load_dir($model->get_value($iter, 2));
        $this->dir_tree->load_dir($model->get_value($iter, 2));

    }

// view menu {
	public function on_view_menu_side_panel_activate ()
    {
        $this->window->widget('side_panel')->set_visible(!$this->window->widget('side_panel')->is_visible());
    }
    
    public function on_view_menu_bottom_panel_activate ()
    {
    	$this->window->widget('bottom_panel')->set_visible(!$this->window->widget('bottom_panel')->is_visible());
	}

// file menu {
    public function on_file_menu_close_activate ()
    {
        $this->window->document_manager->close_document();
    }

    public function on_file_menu_open_activate ()
    {
        $this->window->document_manager->open_document(true);
    }

    public function on_file_menu_new_activate ()
    {
    	$this->window->document_manager->open_document();
    }

    public function on_file_menu_save_activate ()
    {
        $this->window->document_manager->save_document();
    }
    
    public function on_file_menu_save_all_activate ()
    {
    	$this->window->document_manager->save_all();
	}

// edit menu
    public function on_menu_edit_undo_activate ()
    {
        $this->window->document_manager->get_document()->undo();
    }
    
    public function on_menu_edit_redo_activate ()
    {
        $this->window->document_manager->get_document()->redo();
    }
    
    public function on_menu_edit_paste_activate ()
    {
    	$document = $this->window->document_manager->get_document();
    	if ($document instanceof Document)
    	{
    		$document->paste();
		}
	}
	
	public function on_menu_edit_cut_activate ()
    {
    	$document = $this->window->document_manager->get_document();
    	if ($document instanceof Document)
    	{
    		$document->cut();
		}
	}
	
	public function on_menu_edit_copy_activate ()
    {
    	$document = $this->window->document_manager->get_document();
    	if ($document instanceof Document)
    	{
    		$document->copy();
		}    	
	}
}
