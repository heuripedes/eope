<?php

/**
 * DocumentManager.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

require_once(APP_DIR . 'Widgets/Document.php');

class DocumentManager
{
    protected $documents = array();
    protected $untitled_count = 0;
    protected $close_icon = null;
    protected $notebook;

    public function __construct ($notebook)
    {
        $this->notebook = $notebook;
                    
        //$this->connect_after('switch-page', array($this, 'on_change_tab'));

        //$git = GtkIconTheme::get_default();
        //$this->close_icon = GtkImage::new_from_pixbuf($git->load_icon('gtk-close', 16, Gtk::ICON_LOOKUP_USE_BUILTIN));
        
        //$this->set_scrollable(true);
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
        
        $this->check_document_status();
        $this->on_move_cursor();
        
        $app->widget('edit_menu_undo')->set_sensitive($docbuffer->can_undo());
        $app->widget('edit_menu_redo')->set_sensitive($docbuffer->can_redo());
        
        $lang = $document->get_language_name();
        
        $index = $app->get_lang_index($lang);
        $app->widget('lang_combo')->set_active($index);
    }
    
    public function on_document_change ()
    {
        $this->check_document_status();
        $this->on_move_cursor();
    }
    
    public function on_move_cursor ()
    {
        $document = $this->get_document();
        
        if ($document === false)
        {
            return;
        }
        
        $pos = $document->get_cursor_pos();
        if (is_object($pos))
        {
            Etk::get_app()->widget('cursor_pos_label')->set_text("Line:\t".($pos->y+1)." Column:\t".($pos->x+1));
        }        
    }
    
    public function check_document_status ()
    {
        $app = Etk::get_app();
        $document = $this->get_document();
        
        if ($document === false)
        {
            return;
        }
        
        $title = $document->get_title();
        
        if ($document->get_modified())
        {
            $title .= '*';
        }
        
        if ($app->get_title() != $title)
        {
            $app->set_title($title);
            $child = $this->get_nth_page($this->get_current_page());
            $this->set_tab_label_text($child, $title);
        }
        
        
        if (HAS_MBSTRING && $document->get_encoding() != $app->widget('encoding_combo')->get_active_text())
        {
            $i = array_search($document->get_encoding(), $app->valid_encodings);
            $app->widget('encoding_combo')->set_active($i);
        }
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
                Gtk::BUTTONS_YES_NO, 'Do you wish to save this file before close?'
                );
            $win->set_title('File modified');
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
    
        $document = $this->get_document($index === true ? -1 : $index);
        
        if (!$document instanceof Document)
        {
            return false;
        }
        
        $filename = trim($document->get_filename());
        
        if ($filename == '' || $index === true)
        {
            $filename = EtkDialog::save_as();
            
            if ($filename === false)
            {
                return false;
            }
            if ($document->save($filename) !== false)
            {
                $document->set_title(basename($filename));
                $document->get_buffer()->set_modified(false);
                $this->on_change_tab();
                return true;
            }
            return false;
        }

        if ($document->save())
        {
            //$child = $this->get_nth_page($index);
            $document->set_title(basename($filename));
            //$this->set_tab_label_text($child, $document->get_title());
            $document->get_buffer()->set_modified(false);
            $this->on_change_tab();
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
    public function ___changed () {
        echo "insert!! :D\n";
    }
    public function ___changed2 () {
        echo "delete!! :D\n";
    }
    public function open_document ($filename = null)
    {
        $app = Etk::get_app();
        
        //$document = new Document();
        // do some auto detection stuff here
        $document = new ScintillaDocument();
        //$document->load_file('/home/enygmata/work/eope/src/run.phpw');
        //$this->append_page($document->get_widget(), new GtkLabel(basename($document->get_filename())));
        //return;
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
            
            if ($document->load_file() !== true)
            {
                return;
            }
        }
        
        $this->append_page($document->get_widget(), new GtkLabel(basename($document->get_filename())));
        
        return;

        $title = basename($filename);
        
        if ($filename === null)
        {
            $title = sprintf(_('Untitled %i'), ++$this->untitled_count);
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
        
        $document->connect_simple('move-cursor', array($this, 'on_move_cursor'));
        $document->connect_buffer_signal('changed', array($this, 'on_document_change'));
        
        $app->set_title($title);
        
        $language = $document->get_language_name_by_mime($mime);
        
        $index = $app->get_lang_index($language);
        $app->widget('lang_combo')->set_active($index);
        $this->documents[] = $document;
        
        $this->check_document_status();
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
    
    public function refresh_all_options ()
    {
        foreach ($this->documents as $doc)
        {
            $doc->refresh_options();
        }
    }
}
