<?php

class MainWindowSignals extends EtkSignalHandler
{
    public function on_main_window_destroy ()
    {
        $this->application->terminate();
    }
    
    public function on_lang_combo_changed ()
    {
    	$document = $this->window->document_manager->get_document();
    	
    	if ($document === false)
    	{
    		return;
		}
		$options = array('language' => $this->window->widget('lang_combo')->get_active_text());
    	$document->set_options($options);
    }

    public function on_directory_tree_set_focus ()
    {
        $this->window->present();
    }

	public function on_menu_tools_preferences_activate ()
	{
		echo 'aa';
		$this->window->document_manager->open_document(EOPE_ROOT.'/eope.conf');
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
    public function on_btn_tree_new_file_clicked ()
    {
    }

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

// tree events {
    public function on_treeview_button_press_event ($widget, $event)
    {
        if ($event->type != Gdk::_2BUTTON_PRESS || $event->button != 1) // if double-left-click
        {
            return;
        }
        $selection = $this->window->widget('treeview')->get_selection();

        list($model, $iter) = $selection->get_selected();

        if (!$model instanceof GtkTreeStore || !$iter instanceof GtkTreeIter)
        {
            Etk::Trace(__CLASS__, 'Nothing selected');
            return;
        }

        $this->window->document_manager->open_document($model->get_value($iter, 2));
        $this->window->refresh();

    }

// view menu {
	public function on_view_menu_dir_tree_activate ()
    {
        $this->window->widget('vbox3')->set_visible(!$this->window->widget('vbox3')->is_visible());
    }
// file menu {
    public function on_file_menu_close_activate ()
    {
        $this->window->document_manager->close_document();
    }

    public function on_file_menu_open_directory_activate ()
    {
    	$selected_dir = FileDialogs::open_dir($this->window);
        if (isset($selected_dir) && is_dir ($selected_dir))
        {
            //$this->window->dir_tree->show_now();
            $this->dir_tree = new ProjTree($this->window->widget('treeview'));
            $this->dir_tree->load_dir($selected_dir);
        }
        $this->window->refresh();
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
    	//$this->window->document_manager->
	}
}
