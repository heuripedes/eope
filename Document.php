<?php

class Document extends GtkSourceView
{
    protected $tab = -1;
    protected $filename = '';
    protected $title = '';
    protected $lang_name = '';

    public function __construct ()
    {
        parent::__construct();
        $this->buffer = new GtkSourceBuffer();
        $this->set_buffer($this->buffer);
    }

    public function set_parent_tab ($tab)
    {
        $this->tab = $tab;
    }

    public function get_parent_tab ()
    {
        return $this->tab;
    }

    public function get_modified ()
    {
        $this->buffer->get_modified();
    }
    
    public function undo ()
    {
        $this->buffer->undo();
    }

    public function redo ()
    {
        $this->buffer->redo();
    }
    
    public function paste ()
    {
    	$clipboard = new GtkClipboard();
    	$text = $clipboard->wait_for_text();
    	echo $text;    	
	}

    public function get_text ($hidden = true)
    {
        return $this->buffer->get_text($this->buffer->get_start_iter(), $this->buffer->get_end_iter(), $hidden);
    }

    public function set_text ($text, $undoable = false)
    {
    	if ($undoable === true)
    	{
    		$this->buffer->begin_not_undoable_action();
		}
		
        $this->buffer->set_text($text);
        
        if ($undoable === true)
    	{
    		$this->buffer->end_not_undoable_action();
		}
    }

    public function set_filename ($filename)
    {
        $this->filename = $filename;
    }
    
    public function set_title ($title)
    {
    	$this->title = $title;
	}
	
	public function get_title ()
	{
		return $this->title;
	}
    
    public function get_filename()
    {
    	return $this->filename;
	}

    public function get_cursor_pos ()
    {
        
        $cursor_mark = $this->buffer->get_insert();
        $cursor_iter = $this->buffer->get_iter_at_mark($cursor_mark);
        $line = $cursor_iter->get_line();

        $start_iter = $this->buffer->get_iter_at_line($line);
        $line_text = $this->buffer->get_text($start_iter, $cursor_iter);
        $tabs_width = $this->get_tabs_width();
        $column = 0;

        for ($i = 0; $i < strlen($line_text); $i++)
        {
            if ($line_text[$i] == "\n")
            {
                $column += ($tabs_width - ($column % $tabs_width));
            }
            else
            {
                $column++;
            }
        }
		$pos = new GdkRectangle($column,$line,0,0);

        return $pos;
    }

    public function set_language_by_mime ($mimetype)
    {
    	if ($mimetype == 'text/plain')
    	{
    		$this->buffer->set_highlight(false);
    		return;
		}
        $langmngr = new GtkSourceLanguagesManager();
        $lang = $langmngr->get_language_from_mime_type($mimes);

        $this->buffer->set_language($lang);
		$this->buffer->set_highlight(true);
        //$this->lang_name = $lang->get_name();
    }

    public function set_language_by_name ($language = 'none')
    {
        $language =  strtolower($language);
        if ($language == 'none')
        {
            $this->buffer->set_highlight(false);
            return;
        }

        $langmngr = new GtkSourceLanguagesManager();
        $lang_objects = $langmngr->get_available_languages();

        foreach ($lang_objects as $lang)
        {
            if (strtolower($lang->get_name()) == $language)
            {
                $this->buffer->set_language($lang);
		        $this->buffer->set_highlight(true);
                return;
            }
        }
    }

    public function load ()
    {
		if (file_exists($this->filename) && is_readable($this->filename) && is_file($this->filename))
		{
			$text = file_get_contents($this->filename);
			$this->set_text($text, true);
			
			return true;
		}
		return false;
    }
    
    public function save ($filename = null)
    {
    	if ($filename == null)
    	{
    		$filename = $this->filename;
		}
    	if ($filename !=  '')
    	{
    		return file_put_contents($filename, $this->get_text());
		}
		return false;
	}
	
    public function set_options ($options)
    {
        if (isset($options['tab_style']))
        {
            $width = array(1=>2, 2=>3, 3=>4, 4=>8);
            $windex = ($options['tab_style'] > 4 ? $options['tab_style'] -4: $options['tab_style']);
            $this->set_tabs_width($width[$windex]);
            $this->set_insert_spaces_instead_of_tabs($options['tab_style'] > 4);
        }

    	if (isset($options['language']) && is_string($options['language']))
    	{
    		if (strpos($options['language'], '/'))
    		{
    			$this->set_language_by_mime($options['language']);
			}
			else
			{
				$this->set_language_by_name($options['language']);
			}
		}

        if (isset($options['line_numbers']) && $options['line_numbers'] == true)
		{
			$this->set_show_line_numbers(true);
		}

		if (isset($options['line_markers']) && $options['line_markers'] == true)
		{
		    $this->set_show_line_markers(true);
		}

		if (isset($options['auto_indent']) && $options['auto_indent'] == true)
		{
			$this->set_auto_indent(true);
		}

		if (isset($options['smart_keys']) && $options['smart_keys'] == true)
		{
			$this->set_smart_home_end(true);
		}

		// font format: font name:size:style
		if (isset($options['font']) && is_string($options['font']))
		{
			$tokens = explode(':',$options['font']);
			array_map('trim', $tokens);
			$this->modify_font(new PangoFontDescription($tokens[0] . ' ' . $tokens[2] . ' ' .$tokens[1]));
		}
        $this->set_cursor_visible(true);
    }
}
