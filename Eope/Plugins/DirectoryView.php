<?php

class DirectoryViewPlugin extends PluginAbstract
{
    protected $treeview = null;
    protected $buttons = array('refresh'=>null, 'newfile' => null, 'newdir' => null);
    protected $vbox = null;
    protected $swindow = null;
    protected $menu = null;
    protected $label = null;
    
    protected $window = null;
    
    protected $store = null;
    protected $icons = array ();
    protected $txt_renderer = null;
    protected $ico_renderer = null;
    
    protected static $cwd = false;
    
    public function __construct ()
    {
        $this->create_widgets();

        $app = Etk::get_app();
        
        $this->add_to_panel('side', $this->vbox, 'Directory view');
        $this->add_to_menu('file', $this->menu, 2, Gdk::CONTROL_MASK | Gdk::SHIFT_MASK, 'O');
    }
    
    public function on_unload ()
    {
        $this->remove_from_panel('side', $this->vbox);
        $this->remove_from_menu('file', $this->menu);
    }
    
    public function get_handled_events ()
    {
        return array('side_panel_toggle');
    }
    
    public function on_side_panel_toggle ($panel)
    {
        if (!$panel->is_visible())
        {
            return;
        }
        
        if (self::$cwd == '')
        {
            $this->swindow->set_visible(false);
            $this->label->set_visible(true);
        }
        else
        {
            $this->swindow->set_visible(true);
            $this->label->set_visible(false);
        }
    }
    
    public function _on_treeview_button_press_event ($widget, $event)
    {
        if ($event->type != Gdk::_2BUTTON_PRESS || $event->button != 1) // if double-left-click
        {
            return;
        }
        
        $selection = $this->treeview->get_selection();

        list($model, $iter) = $selection->get_selected();

        if (!$model instanceof GtkTreeStore || !$iter instanceof GtkTreeIter)
        {
            Etk::Trace(__CLASS__, 'Nothing selected');
            return;
        }
        
        $path = $model->get_value($iter, 2);
        
        if (is_file($path))
        {
            Etk::get_app()->document_manager->open_document($path);
        }
    }
    
    public function _on_menu_activate ()
    {
        $selected_dir = EtkDialog::open_dir();
        if (isset($selected_dir) && is_dir ($selected_dir))
        {
            $this->swindow->set_visible(true);
            $this->label->set_visible(false);
            $this->load_dir($selected_dir);
        }
        Etk::get_app()->refresh();
    }
    
    public function on_btn_tree_refresh_clicked ()
    {
        $app = Etk::get_app();
        
        $this->treeview->set_cursor_on_cell(array(0));

        $selection = $app->widget('treeview')->get_selection();

        list($model, $iter) = $selection->get_selected();

        if (!$model instanceof GtkTreeStore || !$iter instanceof GtkTreeIter)
        {
            return;
        }

        $this->load_dir($model->get_value($iter, 2));

    }
    
    public static function get_cwd ()
    {
        return self::$cwd;
    }
    
    public function load_dir ($dir)
    {
        $this->treeview->set_model(null);
        $this->store->clear();
        $root = $this->store->append(null, array($this->icons['folder'], basename($dir), $dir));
        
        $this->read_dir($dir, $root);
        
        $this->treeview->set_model($this->store);
        self::$cwd = realpath($dir);
    }
    
    protected function read_dir ($dir, $parent = null)
    {
        $d = opendir($dir);
        $files = $dirs = array();
        $entries = array();
        while($e = readdir($d))
        {
            if (in_array($e, array('.', '..')))
            {
                continue;
            }
            $path = $dir . '/' .$e;
            
            if (is_dir($path))
            {
                $dirs[] = $path;
            }
            elseif(is_file($path))
            {
                $files[] = $path;
            }
        }
        
        $total = count($dirs);

        for ($i=0;$i<$total; $i++)
        {
            $child = $this->store->append($parent, array($this->icons['folder'], basename($dirs[$i]), $dirs[$i]));
            if (basename($dirs[$i]) == '..')
            {
                continue;
            }
            $this->read_dir($dirs[$i], $child);
        }


        $total = count($files);

        for ($i=0;$i<$total; $i++)
        {
            echo $files[$i] ."\n";
            $this->store->append($parent, array($this->icons['file'], basename($files[$i]), $files[$i]));
        }
    }
    
    protected function create_widgets ()
    {
        $this->treeview = new GtkTreeView();
        
        $text_renderer = new GtkCellRendererText();
        $icon_renderer = new GtkCellRendererPixbuf();

        $column1 = new GtkTreeViewColumn();
        $column2 = new GtkTreeViewColumn();

        $column1->pack_start($icon_renderer, false);
        $column1->set_attributes($icon_renderer, 'pixbuf', 0);

        $column1->pack_start($text_renderer, false);
        $column1->set_attributes($text_renderer, 'text', 1);
        //$column1->set_title('Files');
        
        $column2->pack_start($text_renderer, false);
        $column2->set_attributes($text_renderer, 'text', 2);
        $column2->set_visible(false);

        $this->txt_renderer = $text_renderer;
        $this->ico_renderer = $icon_renderer;

        $git = GtkIconTheme::get_default();

        $this->icons['folder']  = $git->load_icon(Gtk::STOCK_DIRECTORY, 16, Gtk::ICON_LOOKUP_USE_BUILTIN);
        $this->icons['menu']    = $git->load_icon(Gtk::STOCK_OPEN,      16, Gtk::ICON_LOOKUP_USE_BUILTIN);
        $this->icons['file']    = $git->load_icon(Gtk::STOCK_FILE,      16, Gtk::ICON_LOOKUP_USE_BUILTIN);
        $this->icons['refresh'] = $git->load_icon(Gtk::STOCK_REFRESH,   16, Gtk::ICON_LOOKUP_USE_BUILTIN);
        
        $this->treeview->append_column($column1);
        $this->treeview->append_column($column2);
        
        $this->store = new GtkTreeStore(GObject::TYPE_OBJECT, GObject::TYPE_STRING, GObject::TYPE_STRING);
        $this->treeview->set_model($this->store);
        
        $this->label = new GtkLabel("Choose a directory pressing Ctrl+Shift+O");
        $this->label->modify_font(new PangoFontDescription('14'));
        $this->vbox = new GtkVBox();
        
        $this->swindow = new GtkScrolledWindow ();
        $this->swindow->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
        $this->swindow->add($this->treeview);
        
        $this->vbox->pack_start($this->swindow);
        $this->vbox->pack_start($this->label, true, true);
        
        $this->swindow->set_visible(false);
        
        $this->label->set_visible(true);
        $this->label->set_justify(Gtk::JUSTIFY_CENTER);
        $this->label->set_line_wrap(true);
        $this->label->set_size_request(180, -1);
        
        //$this->treeview->set_size_request(150, -1);
        $this->swindow->set_size_request(180, -1);
        //$this->vbox->set_size_request(180, -1);
        
        $this->vbox->show();
        
        $this->menu = new GtkImageMenuItem('Open directory');
        $this->menu->set_image(GtkImage::new_from_pixbuf($this->icons['menu']));
        $this->menu->connect_simple('activate', array($this, '_on_menu_activate'));
        $this->treeview->connect('button-press-event', array($this, '_on_treeview_button_press_event'));
    }
}
