<?php

class MainWindowSignals extends EtkSignalHandler
{
    public function on_main_window_destroy ()
    {
    	$conf = new ConfigurationManager();
    	$size = $this->window->get_size();
    	$conf->set('ui.width', $size[0]);
    	$conf->set('ui.height', $size[1]);
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
		$conf = new ConfigurationManager();
		$n = $this->window->widget('tab_combo')->get_active();
		
		if ($n > 3)
		{
			$conf->set('editor.indent.spaces', true);
		}
		else
		{
			$conf->set('editor.indent.spaces', false);
		}
		
		$width = array(2, 3, 4, 8);
		$conf->set('editor.tab_style', $n);// ($n > 3 ? $width[$n-4] : $width[$n]));
		$document->refresh_options();
	}

    public function on_directory_tree_set_focus ()
    {
        $this->window->present();
    }

// tools menu
	public function on_menu_tools_paste_php_activate ()
	{
		$document = $this->window->document_manager->get_document();
		
		if ($document === false)
		{
			return;
		}
		
		$body ="parent_pid=&format=php";
		$body .= "&code2=".$document->get_text();
		$body .= "&poster=Eope&paste=Send&remember=1&expiry=m&email=";
		
		$request = "POST /pastebin.php HTTP/1.0\r\n";
		$request .= "Host: php.pastebin.com\r\n";
		$request .= "Accept: */*;q=0.1\r\n";
		$request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$request .= "Content-length: " . strlen($body). "\r\n\r\n";
		$request .= $body;
		$request .= "\r\n";
		
		$fp = fsockopen('php.pastebin.com', 80, $errno, $errstr, 30);
		
		$title = 'Error';
		$message = "Cannot paste your code.\n$errno: $errstr\n";
		$type = Gtk::MESSAGE_ERROR;
		
		if ($fp !== false)
		{
			fwrite($fp, $request);

			$response = '';
			while (!feof($fp))
			{
				$response .= fread($fp, 1024);
			}
			fclose($fp);

			preg_match('/Location: http:\/\/php.pastebin.com\/([a-z0-9]+)/', $response, $results);
			
			if (isset($results[1]))
			{
				$title = 'Paste sucessful';
				$message = "Your code has been pasted. Check it at:\n\nhttp://php.pastebin.com/".$results[1];
				$type = Gtk::MESSAGE_INFO;
			}
			else
			{
				$message = "Cannot paste your code.";
			}
		}
		
		$dialog = new GtkMessageDialog (
			$this->window->get_window(),
			Gtk::DIALOG_MODAL, 
			$type,
			Gtk::BUTTONS_OK,
			$message
		);
		$dialog->set_title($title);
		$dialog->run();
		$dialog->destroy();
	}
	
	public function on_menu_tools_preferences_activate ()
	{
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
	public function on_view_side_panel_activate ()
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
