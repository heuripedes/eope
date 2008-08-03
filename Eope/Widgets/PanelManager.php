<?php

class PanelManager extends GtkNotebook
{
    protected $panels = array();
    
    public function __construct ()
    {
        parent::__construct();
    }
    
    protected function update ()
    {
        $this->set_show_tabs($this->get_n_pages() > 1);
    }
    
    public function set_visible ($visible, $all = true)
    {
        echo "visible = $visible\n";
        $this->update();
        if ($this->get_n_pages() < 1)
        {
            $this->set_visible(false);
        }
        else
        {
            parent::set_visible($visible, $all);
        }
    }
    
    public function add_panel (GtkWidget $child, $label = '')
    {
        $this->panels[] = $child;
        
        if ($label == '')
        {
            $label = 'Page '.count($this->panels);
        }
        //echo "[PanelManager] Adding $label\n";
        $this->update();
        $page = $this->append_page($child, new GtkLabel($label));
        $this->show_all();
        return $page;
    }
    
    public function remove_panel ($index)
    {
        if ($index instanceof GtkWidget)
        {
            $index = $this->page_num($index);
        }
        //echo "[PanelManager] Removing $index\n";
        $this->remove_page($index);
        unset($this->panels[$index]);
        sort($this->panels);
        $this->update();
    }
}
