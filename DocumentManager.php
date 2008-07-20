<?php

require_once('Document.php');

class DocumentManager extends GtkNotebook
{
    protected $documents = array();
    protected $mainwindow = null;
    protected $application = null;
    protected $config = null;
    protected $untitled_count = 0;
    protected $close_icon = null;

    public function __construct (Eope $application, MainWindow $window)
    {
        parent::__construct();
        $this->application = $application;
        $this->mainwindow = $window;

        $this->connect_after('switch-page', array($this, 'on_change_tab'));

        $git = GtkIconTheme::get_default();
        $this->close_icon = GtkImage::new_from_pixbuf($git->load_icon('gtk-close', 16, Gtk::ICON_LOOKUP_USE_BUILTIN));
        
        $this->set_scrollable(true);
    }

    public function on_change_tab ()
    {
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
        
    	$pos = $document->get_cursor_pos();
    	
    	$this->mainwindow->widget('line_label')->set_text("Line:\t" . ($pos->y+1));
    	$this->mainwindow->widget('column_label')->set_text("Column:\t" . ($pos->x+1));
    	
    	$this->mainwindow->widget('menu_edit_undo')->set_sensitive($docbuffer->can_undo());
    	$this->mainwindow->widget('menu_edit_redo')->set_sensitive($docbuffer->can_redo());
		
		$title = $document->get_title();
    	
    	if ($document->get_modified())
    	{
    		$title .= '*';
		}
    	
    	$this->mainwindow->set_title($title);
    	$child = $this->get_nth_page($this->get_current_page());
		$this->set_tab_label_text($child, $title);
    	
    	$lang = $document->get_language_name();
    	
    	$index = $this->application->get_lang_index($lang);
        $this->mainwindow->widget('lang_combo')->set_active($index);
        
    }
    
    public function on_document_change ()
    {
    	$this->on_change_tab();
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
        	$this->mainwindow->activate_widgets();
        	$this->mainwindow->set_title('Eope');
		}
    }
    
    public function get_document ($index = -1)
    {
        if ($index == -1)
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
    		$filename = FileDialogs::save_as($this->mainwindow);
    		
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
        $document = new Document();
        
        if ($filename === true)
        {
        	$filename = FileDialogs::open_file($this->mainwindow);
        	
        	if ($filename === false)
        	{
        		return;
			}
		}
		
		if (is_string($filename) && strlen($filename) > 0)
        {
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
        }
        
        $document->set_title($title);

        $swindow = new GtkScrolledWindow ();
        $swindow->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
        $swindow->add($document);
        

        $page = $this->append_page($swindow, new GtkLabel($title));
        
        $this->mainwindow->activate_widgets(true); 

        $ext = explode('.', $filename);
        
        switch(strtolower(end($ext)))
        {
        	// scripting
        	case 'pl': $language = 'perl'; break;
        	case 'py': $language = 'python'; break;
        	case 'rb': $language = 'ruby'; break;
        	case 'php': $language = 'php'; break;
        	case 'sh': $language = 'sh'; break;
        	case 'javascript':
        	case 'js': $language = 'javascript'; break;
        	
        	// markup
        	case 'htm': case 'xhtm': case 'xhtml':
        	case 'html': $language = 'html'; break;
        	case 'xml': $language = 'xml'; break;
        	
        	// style
        	case 'css': $language = 'css'; break;
        	
        	// other
        	case 'ini': $language = '.ini'; break;
        	case 'conf': $language = '.ini'; break;
        	case 'pas': $language = 'pascal'; break;
        	case 'java': $language = 'java'; break;
        	case 'cs': $language = 'c#'; break;
        	case 'h':
        	case 'c': $language = 'c'; break;
        	case 'hxx':
        	case 'cpp': $language = 'c++'; break;
        	
        	default: $language = 'none';
		}
                
        $document->set_language_by_name($language);
        $document->refresh_options();
            
		$this->show_all();

		//$this->set_tab_reorderable($swindow, true);
        $this->set_current_page($page);
        
        $document->grab_focus();
        //$document->set_parent_tab($this->get_current_page());
        
        $document->connect_simple('move-cursor', array($this, 'on_document_change'));
		$document->get_buffer()->connect_simple('changed', array($this, 'on_document_change'));
		
		$this->mainwindow->set_title($title);
		$this->on_change_tab();
		
    	$index = $this->application->get_lang_index($language);
		$this->mainwindow->widget('lang_combo')->set_active($index);
		
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
}

?>
