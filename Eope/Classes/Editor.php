<?php

/**
 * Editor.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

class Editor
{
    protected $documents = array();
    protected $untitled_count = 0;
    protected $close_icon = null;
    protected $notebook;

    public function __construct (GtkWidget $notebook)
    {
        $this->notebook = $notebook;
                    
        $this->notebook->connect_after('switch-page', array($this, 'on_change_tab'));

        //$git = GtkIconTheme::get_default();
        //$this->close_icon = GtkImage::new_from_pixbuf($git->load_icon('gtk-close', 16, Gtk::ICON_LOOKUP_USE_BUILTIN));
        
        $this->notebook->set_scrollable(true);
		//$this->open_file('run.phpw');
    }

    public function on_change_tab ()
    {
        $app = Etk::get_app();
        
        //$lang = $document->get_language_name();
        
        //$index = $app->get_lang_index($lang);
        //$app->widget('lang_combo')->set_active($index);
		$app->activate_widgets(true);
    }
    
    public function on_document_change ()
    {
        $this->check_document_status();
        $this->on_move_cursor();
    }
    
    public function close_document ($page = -1)
    {
        if ($page < 0)
        {
            $page = $this->get_current_page();
        }
        
        if (!isset($this->documents[$page]))
        {
            return;
        }
        
        $app = Etk::get_app();
        $doc = $this->documents[$page];
        
        if ($doc->get_modified())
        {
            $win = new GtkMessageDialog($app->get_window(),
                Gtk::DIALOG_MODAL, Gtk::MESSAGE_QUESTION,
                Gtk::BUTTONS_YES_NO, _('Do you wish to save this file before close?')
                );
            $win->set_title(_('File modified'));
            $win->show_all();
            
            if ($win->run() == Gtk::RESPONSE_YES)
            {
                $app->document_manager->save_document();
            }
            
            $win->destroy();
        }
        
        unset($this->documents[$page]);
        $this->remove_page($page);
        $this->documents = array_values($this->documents);
        
        if (!$this->get_n_pages())
        {
            Etk::get_app()->activate_widgets();
            Etk::get_app()->set_title('Eope');
        }
    }
    
    public function get_document ($index = -1)
    {
        if ($index < 0)
        {
            $index = $this->notebook->get_current_page();
        }
        
        if (!isset($this->documents[$index]))
        {
            return false;
        }
        
        return $this->documents[$index];
    }
    
    public function save_all ()
    {
        for ($i=count($this->documents);$i--;)
            $this->save_document($i);
    }
    
    public function save_document ($index = -1)
    {
    
        $document = $this->get_document($index === true ? -1 : $index);
        
        if (!is_array($document))
        {
            return false;
        }
        
        $filename = trim($document['obj']->get_filename());
        
        if ($filename == '' || $index === true)
        {
            $filename = EtkDialog::save_as();
            
            if ($filename === false)
            {
                return false;
            }
            if ($document['obj']->save_file($filename) !== false)
            {
                $document['title'] = basename($filename);
                $document['obj']->set_modified(false);
                $this->on_doc_ui_update($document['obj']);
                return true;
            }
            return false;
        }

        if ($document['obj']->save_file())
        {
            //$child = $this->get_nth_page($index);
            $document['title'] = basename($filename);
            //$this->set_tab_label_text($child, $document->get_title());
            $document['obj']->set_modified(false);
            $this->on_doc_ui_update($document['obj']);
            return true;
        }
        
        return false;
    }
    

    /**
     * 
     * if filename = null: create new
     * if filename = true: show open dialog
     * if filename = string: open file
     * 
     * @param string|boolean|null $filename
     */

    public function open_file ($filename = false)
    {
        $app = Etk::get_app();
        
        $document = new Document();

        // TODO: check this
        if ($filename !== false)
        {
            if (($page = array_search($filename, $this->get_open_files())) !== false)
            {
                $this->set_current_page($page);
                return;
            }
            
            $document->set_filename($filename);
            
            if ($document->load_file() !== true)
            {
                return;
            }

            $title = basename($filename);
        }
        else
        {
            $title = sprintf(_('Untitled %i'), ++$this->untitled_count);
        }
        
		$this->documents[] = array(
			'obj' => $document,
			'title' => $title,
			'label' => new GtkLabel($title)
		);
		$doc = end($this->documents);
		
        $page = $this->notebook->append_page($document->get_widget(), $doc['label']);

        $ext = @end(explode('.', $filename));
        
        
        // TODO: detect & set language
            
        $this->notebook->show_all();
        $this->notebook->set_current_page($page);
        
        $document->set_focus();
		
		$document->connect('ui-update', array($this, 'on_doc_ui_update'));
    }
	
	public function find_doc_by_object ($doc)
	{
		foreach ($this->documents as $d)
		{
			if ($doc === $d['obj'])
			{
				return $d;
			}
		}
		return false;
	}
	
	public function on_doc_ui_update ($docobj)
	{
        $pos = $docobj->get_cursor_pos();
		$app = Etk::get_app();
		$str = _('Line').":\t".($pos->line+1).' '._('Column').":\t".($pos->column+1);
        $app->widget('cursor_pos_label')->set_text($str);
		
		$doc = $this->find_doc_by_object($docobj);
		
		if ($doc === false)
		{
			return;
		}
		
		$title = $doc['title'];
		
		$app = Etk::get_app();

        if ($docobj->get_modified())
        {
            $title .= '*';
        }
        
        if ($app->get_title() != $title)
        {
            $app->set_title($title);
			$doc['label']->set_text($title);
        }
		
		$app->widget('edit_menu_undo')->set_sensitive($docobj->can_undo());
        $app->widget('edit_menu_redo')->set_sensitive($docobj->can_redo());
        
        if (HAS_MBSTRING && $docobj->get_encoding() != $app->widget('encoding_combo')->get_active_text())
        {
            $i = array_search($docobj->get_encoding(), $app->valid_encodings);
            $app->widget('encoding_combo')->set_active($i);
        }
	}

    public function get_open_files ()
    {
        $files = array();
        foreach ($this->documents as $doc)
        {
            $files[] = $doc['obj']->get_filename();
        }
        return $files;
    }
    
    public function get_modified_files ()
    {
        $files = array();
        foreach ($this->documents as $document)
        {
            if ($document->get_modified())
            {
                $files[] = $document->get_filename();
            }
        }
        return $files;
    }
    
    public function refresh_all_options ()
    {
        foreach ($this->documents as $doc)
        {
            $doc->refresh_options();
        }
    }
}
