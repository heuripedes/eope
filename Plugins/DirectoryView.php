<?php

class DirectoryViewPlugin extends Plugin
{
	protected $treeview = null;
	protected $buttons = array('refresh'=>null, 'newfile' => null, 'newdir' => null);
	protected $vbox = null;
	protected $swindow = null;
	protected $menu = null;
	
	protected $window = null;
	
	protected $store = null;
    protected $icons = array ();
    protected $txt_renderer = null;
    protected $ico_renderer = null;
    
    protected static $cwd = '.';
	
	public function __construct ()
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
		$column1->set_title('Files');
		
		$column2->pack_start($text_renderer, false);
		$column2->set_attributes($text_renderer, 'text', 2);
        $column2->set_visible(false);

        $this->txt_renderer = $text_renderer;
        $this->ico_renderer = $icon_renderer;

        $git = GtkIconTheme::get_default();

        $this->icons['folder'] = $git->load_icon('gtk-directory', 16, Gtk::ICON_LOOKUP_USE_BUILTIN);
        $this->icons['menu'] = $git->load_icon('gtk-open', 13.5, Gtk::ICON_LOOKUP_USE_BUILTIN);
        $this->icons['folder_open'] = $git->load_icon('gtk-open', 16, Gtk::ICON_LOOKUP_USE_BUILTIN);
        $this->icons['file'] = $git->load_icon('gtk-file', 16, Gtk::ICON_LOOKUP_USE_BUILTIN);
        $this->icons['refresh'] = $git->load_icon('gtk-file', 16, Gtk::ICON_LOOKUP_USE_BUILTIN);
        
        $icon_renderer->set_property('pixbuf-expander-open', $this->icons['folder_open']);
        $icon_renderer->set_property('pixbuf-expander-closed', $this->icons['folder']);

        $this->treeview->append_column($column1);
		$this->treeview->append_column($column2);
		
		$this->store = new GtkTreeStore(GObject::TYPE_OBJECT, GObject::TYPE_STRING, GObject::TYPE_STRING);
        $this->treeview->set_model($this->store);
        
        $this->vbox = new GtkVBox();
		
		$this->swindow = new GtkScrolledWindow ();
        $this->swindow->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
        $this->swindow->add($this->treeview);
        $this->vbox->pack_start($this->swindow);
        
        $this->menu = new GtkImageMenuItem('Open directory');
        $this->menu->set_image(GtkImage::new_from_pixbuf($this->icons['menu']));
        $this->menu->connect_simple('activate', array($this, '_on_menu_activate'));
        $this->treeview->connect('button-press-event', array($this, '_on_treeview_button_press_event'));

	}
	
	public function get_handled_events ()
	{
		return array('main_window_create');
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

        $this->window->document_manager->open_document($model->get_value($iter, 2));
    }
    
	public function _on_menu_activate ()
	{
		$selected_dir = FileDialogs::open_dir($this->window);
        if (isset($selected_dir) && is_dir ($selected_dir))
        {
            $this->load_dir($selected_dir);
        }
        $this->window->refresh();
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
            $this->read_dir($dirs[$i], $child);
        }

        $total = count($files);

        for ($i=0;$i<$total; $i++)
        {
            $this->store->append($parent, array($this->icons['file'], basename($files[$i]), $files[$i]));
        }
	}
	
	public function on_main_window_create (MainWindow $window)
	{
		//$window = $args[0];

		$window->sidepanel_manager->add_panel($this->vbox,'Directory view');
		$window->sidepanel_manager->show_all();
		
		$accel = $window->get_accel_group();
		$this->menu->add_accelerator('activate', $accel, ord('o'),
			Gdk::CONTROL_MASK | Gdk::SHIFT_MASK, Gtk::ACCEL_VISIBLE);
        
		
		$window->widget('file_menu_menu')->add($this->menu);
		$window->widget('file_menu_menu')->reorder_child($this->menu, 2);
		//$this->glade->get_widget('window')->remove($this->glade->get_widget('vbox5'));
		$window->widget('file_menu_menu')->show_all();
		$this->window = $window;
	}
}
