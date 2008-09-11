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
    
    public function toggle ()
    {
        $this->set_visible(!$this->is_visible());
    }
    
    public function add_panel (GtkWidget $child, $label = '')
    {
        $this->panels[] = $child;
        
        if ($label == '')
        {
            $label = 'Page '.count($this->panels);
        }
        
        $this->update();
        $page = $this->append_page($child, new GtkLabel($label));
        $this->show();
        return $page;
    }
    
    public function remove_panel ($index)
    {
        if ($index instanceof GtkWidget)
        {
            $index = $this->page_num($index);
        }
        
        $this->remove_page($index);
        unset($this->panels[$index]);
        sort($this->panels);
        $this->update();
    }
}
