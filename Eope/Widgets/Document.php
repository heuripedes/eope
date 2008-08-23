<?php

/**
 * Document.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

class Document extends GtkSourceView
{
    protected $tab = -1;
    protected $filename = '';
    protected $title = '';
    protected $lang_name = '';
    protected $options = array();
    protected $encoding = '';
    
    

    public function __construct ()
    {
        parent::__construct();
        $buffer = new GtkSourceBuffer();
        $this->set_buffer($buffer);
        $this->buffer = $this->get_buffer();
        $this->encoding = strtoupper(ini_get('php-gtk.codepage'));
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
        return $this->buffer->get_modified();
    }
    
    public function set_modified ($modified = true)
    {
        $this->buffer->set_modified($modified);
    }
    
    public function connect_buffer_signal ($event, $function, $param=array())
    {
        $this->buffer->connect($event, $function, $param);
    }
    
    public function undo ()
    {
        if ($this->buffer->can_undo())
        {
            $this->buffer->undo();
        }
        else
        {
            $this->set_modified(false);
        }
    }

    public function redo ()
    {
        $this->buffer->redo();
    }
    
    public function paste ()
    {
        $this->buffer->paste_clipboard(new GtkClipboard(), null, True);
    }
    
    public function cut ()
    {
        $this->buffer->cut_clipboard(new GtkClipboard(), true);
    }
    
    public function copy ()
    {
        $this->buffer->copy_clipboard(new GtkClipboard());
    }

    public function get_text ($hidden = true)
    {
        return $this->buffer->get_text($this->buffer->get_start_iter(),
                $this->buffer->get_end_iter(), $hidden);
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
    
    public function get_encoding ()
    {
        return $this->encoding;
    }
    
    public function set_encoding ($encoding = null)
    {
        if ($encoding === null)
        {
            return;
        }
        $this->encoding = $encoding;
        $this->set_modified(true);
    }
    
    public function get_filename()
    {
        return $this->filename;
    }

    public function get_cursor_pos ()
    {
        if (!$this->buffer instanceof GtkSourceBuffer)
        {
            return;
        }
        
        $cursor_mark = $this->buffer->get_insert();
        $cursor_iter = $this->buffer->get_iter_at_mark($cursor_mark);
        $line = $cursor_iter->get_line();

        $start_iter = $this->buffer->get_iter_at_line($line);
        
        try {
            $line_text = $this->buffer->get_text($start_iter, $cursor_iter);
        }
        catch (Exception $e)
        {
            echo '['.basename($this->filename).'] ' . $e->getMessage()."\n";
            return false;
        }
        
        $tabs_width = $this->get_tabs_width();
        $column = 0;

        for ($i = 0; $i < strlen($line_text); $i++)
        {
            if ($line_text[$i] == "\t")
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

    public function get_language_name ()
    {
        $lang = $this->buffer->get_language();
        if ($lang instanceof GtkSourceLanguage)
        {
            return $lang->get_name();
        }
        return 'None';
    }

    public function get_language_mime ()
    {
        $lang = $this->buffer->get_language();
        if ($lang instanceof GtkSourceLanguage)
        {
            $mimes = $lang->get_mime_types();
            return $mimes[0];
        }
        return 'text/plain';
    }

    public function set_language_by_mime ($mimetype)
    {
        $this->buffer->set_language(null);
        if ($mimetype == 'text/plain')
        {
            return;
        }
        $langmngr = new GtkSourceLanguagesManager();
        $lang = $langmngr->get_language_from_mime_type($mimetype);

        $this->buffer->set_language($lang);
        $this->buffer->set_highlight(true);
    }
    
    public function get_language_name_by_mime ($mime)
    {
        if ($mime == 'text/plain')
        {
            return 'None';
        }
        $langmngr = new GtkSourceLanguagesManager();
        $lang = $langmngr->get_language_from_mime_type($mime);
        return $lang->get_name();
    }

    public function set_language_by_name ($language = 'none')
    {
        $language =  strtolower($language);
        $this->buffer->set_language(null);
        if ($language == 'none')
        {
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
            //echo iconv('UTF-8', 'ASCII//TRANSLIT', $text);
            
            if (HAS_MBSTRING)
            {
                $this->encoding = mb_detect_encoding($text);
                echo "$this->encoding\n";
                $text = mb_convert_encoding($text, ini_get('php-gtk.codepage'), $this->encoding);
            }
            
            $this->set_text($text, true);
            $this->set_modified(false);
            
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
            $text = $this->get_text();
            if (HAS_MBSTRING)
            {
                $text = mb_convert_encoding($text, $this->encoding, ini_get('php-gtk.codepage'));
            }
            return file_put_contents($filename, $text);
        }
        return false;
    }
    
    public function get_options ()
    {
        return $this->options;
    }
    
    public function refresh_options ()
    {
        $modified = $this->get_modified();
        $conf = Etk::get_config();
        $n = $conf->get('editor.tab_style') ;
        $width = array(2, 3, 4, 8);
        $this->set_tabs_width(($n > 3 ? $width[$n-4] : $width[$n]));
        $this->set_insert_spaces_instead_of_tabs($conf->get('editor.tab_style') > 3);
                
        if ($conf->get('editor.word_wrap') == true)
        {
            $this->set_wrap_mode(GTK::WRAP_WORD);
        }
        $this->set_show_margin((bool)$conf->get('editor.margin'));
        $this->set_highlight_current_line((bool)$conf->get('editor.highlight_line'));
        $this->set_show_line_numbers((bool)$conf->get('editor.line_numbers'));
        $this->set_show_line_markers((bool)$conf->get('editor.line_markers'));
        $this->set_auto_indent((bool)$conf->get('editor.autoindent'));
        
        $this->set_smart_home_end((bool)$conf->get('editor.smart_keys'));
        $this->modify_font(new PangoFontDescription($conf->get('editor.font')));
        
        $this->set_cursor_visible(true);
        
        $this->set_modified($modified);
    }
}
