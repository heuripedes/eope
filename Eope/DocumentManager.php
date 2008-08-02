<?php

require_once('Document.php');
require_once('Mimes.php');

class DocumentManager extends GtkNotebook
{
    protected $documents = array();
    protected $untitled_count = 0;
    protected $close_icon = null;

    public function __construct ()
    {
        parent::__construct();

        $this->connect_after('switch-page', array($this, 'on_change_tab'));

        $git = GtkIconTheme::get_default();
        $this->close_icon = GtkImage::new_from_pixbuf($git->load_icon('gtk-close', 16, Gtk::ICON_LOOKUP_USE_BUILTIN));
        
        $this->set_scrollable(true);
    }

    public function on_change_tab ()
    {
    	$app = Etk::get_app();
    	$document = $this->get_document();
    	
    	if ($document === false)
    	{
    		return;
		}
		
    	$docbuffer = $document->get_buffer();
    	
    	if (!$docbuffer instanceof GtkSourceBuffer)
    	{
    	    return;
        }
        
    	$this->update_status();
    	
    	Etk::get_app()->widget('menu_edit_undo')->set_sensitive($docbuffer->can_undo());
    	Etk::get_app()->widget('menu_edit_redo')->set_sensitive($docbuffer->can_redo());
		
    	$lang = $document->get_language_name();
    	
    	$index = $app->get_lang_index($lang);
        $app->widget('lang_combo')->set_active($index);
        
    }
    
    public function update_status ()
    {
    	$document = $this->get_document();
    	
    	if ($document === false)
    	{
    		return;
		}
		
    	$this->update_cursor_pos();
    	
    	$title = $document->get_title() . ($document->get_modified() ? '*' : '');
    	Etk::get_app()->set_title($title);
    	$child = $this->get_nth_page($this->get_current_page());
		$this->set_tab_label_text($child, $title);
	}
	
	public function update_cursor_pos ()
	{
		$document = $this->get_document();
    	
    	if ($document === false)
    	{
    		return;
		}
		
    	$pos = $document->get_cursor_pos();
    	
    	//Etk::get_app()->widget('line_label')->set_text("Line:\t" . ($pos->y+1));
    	//Etk::get_app()->widget('column_label')->set_text("Column:\t" . ($pos->x+1));
    	Etk::get_app()->widget('cursor_pos_label')->set_text("Line:\t".($pos->y+1)." Column:\t".($pos->x+1));
	}

    public function close_document ($index = -1)
    {
        if ($index == -1)
        {
            $page = $this->get_current_page();

            if ($page < 0)
            {
                return;
            }
        }
        unset($this->documents[$page]);
        $this->remove_page($page);
        sort($this->documents);
        
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
            $index = $this->get_current_page();
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
		$document = $this->get_document($index);
    	$filename = trim($document->get_filename());
    	
    	if ($filename == '' || $index === true)
    	{
    		$filename = EtkDialog::save_as();
    		
    		if ($filename === false)
    		{
    			return;
			}
			if ($document->save($filename) !== false)
			{
				$child = $this->get_nth_page($index);
				$document->set_filename($filename);
				$document->set_title(basename($filename));
				//$this->set_tab_label_text($child, $document->get_title());
				$this->on_change_tab();
				$document->get_buffer()->set_modified(false);
			}
			return;
		}

    	if ($document->save())
    	{
    	    $child = $this->get_nth_page($index);
    		$document->set_title(basename($filename));
			$this->set_tab_label_text($child, $document->get_title());
			$document->get_buffer()->set_modified(false);
		}
	}
	

	/**
	 * 
	 * if filename = null: create new
	 * if filename = true: show open dialog
	 * if filename = string: open file
	 * 
	 * @param string|boolean|null $filename
     */
    public function open_document ($filename = null)
    {
    	$app = Etk::get_app();
    	
        $document = new Document();
        
        if ($filename === true)
        {
        	$filename = EtkDialog::open_file();
        	
        	if ($filename === false)
        	{
        		return;
			}
		}
		
		if (is_string($filename) && strlen($filename) > 0)
        {
        	if (($page = array_search($filename, $this->get_open_files())) !== false)
			{
				$this->set_current_page($page);
				return;
			}
			
        	$document->set_filename($filename);
        	
            if ($document->load() === false)
            {
            	return;
            }
        }

        $title = basename($filename);
		
        if ($filename === null)
        {
            $title = 'Untitled ' . (++$this->untitled_count);
            $document->set_modified(true);
        }
        
        $document->set_title($title);
		
        $swindow = new GtkScrolledWindow ();
        $swindow->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
        $swindow->add($document);

        $page = $this->append_page($swindow, new GtkLabel($title));
        
        Etk::get_app()->activate_widgets(true); 

        $ext = @end(explode('.', $filename));
        
        $mime = get_mime_by_ext($ext);
        $document->set_language_by_mime($mime);
        $document->refresh_options();
            
		$this->show_all();

        $this->set_current_page($page);
        
        $document->grab_focus();
        
        $document->connect_simple('move-cursor', array($this, 'update_cursor_pos'));
		$document->get_buffer()->connect_simple('changed', array($this, 'update_status'));
		
		$app->set_title($title);
		
		$language = $document->get_language_name_by_mime($mime);
		
    	$index = $app->get_lang_index($language);
		$app->widget('lang_combo')->set_active($index);
        $this->documents[] = $document;
    }

    public function get_open_files ()
    {
        $files = array();
        foreach ($this->documents as $document)
        {
            $files[] = $document->get_filename();
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
}
