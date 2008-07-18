<?php

class ProjTree
{
	public $treeview = null;
	protected $store = null;
    protected $icons = array ();
    protected $txt_renderer = null;
    protected $ico_renderer = null;
    protected $root = null;

	public function __construct (GtkTreeview $treeview)
	{
		$this->treeview = $treeview;

		$text_renderer = new GtkCellRendererText();
        $icon_renderer = new GtkCellRendererPixbuf();

        $column1 = new GtkTreeViewColumn();
		$column2 = new GtkTreeViewColumn();

        $column1->pack_start($icon_renderer, false);
		$column1->set_attributes($icon_renderer, 'pixbuf', 0);

        $column1->pack_start($text_renderer, false);
		$column1->set_attributes($text_renderer, 'text', 1);

		$column2->pack_start($text_renderer, false);
		$column2->set_attributes($text_renderer, 'text', 2);
        $column2->set_visible(false);

        $this->txt_renderer = $text_renderer;
        $this->ico_renderer = $icon_renderer;

        $git = GtkIconTheme::get_default();

        $this->icons['folder'] = $git->load_icon('gtk-directory', 16, Gtk::ICON_LOOKUP_USE_BUILTIN);
        $this->icons['folder_open'] = $git->load_icon('gtk-open', 16, Gtk::ICON_LOOKUP_USE_BUILTIN);
        $this->icons['file'] = $git->load_icon('gtk-file', 16, Gtk::ICON_LOOKUP_USE_BUILTIN);
        
        $icon_renderer->set_property('pixbuf-expander-open', $this->icons['folder_open']);
        $icon_renderer->set_property('pixbuf-expander-closed', $this->icons['folder']);

        $treeview->append_column($column1);
		$treeview->append_column($column2);
		
		$this->store = new GtkTreeStore(GObject::TYPE_OBJECT, GObject::TYPE_STRING, GObject::TYPE_STRING);// 80 = Gtk::TYPE_OBJECT, 64 = Gtk::TYPE_STRING
        $this->treeview->set_model($this->store);
	}
	public function load_dir ($dir)
	{
        $this->treeview->set_model(null);
        $this->store->clear();
		$root = $this->store->append(null, array($this->icons['folder'], basename($dir), $dir));
        
		$this->read_dir($dir, $root);
		
		$this->treeview->set_model($this->store);
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
}

?>
