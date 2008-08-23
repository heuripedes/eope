<?php

/**
 * SearchDialog.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

require_once(APP_DIR . 'SearchDialogSignals.php');

class SearchDialog extends EtkWindow
{
    public function __construct($replace = false)
    {
        parent::__construct(APP_DIR . 'Glade/search.glade', 'window');
        //$this->connect_glade_to($this);
        $this->set_transient_for(Etk::get_app()->get_window());
        
        if (!$replace)
        {
            $this->widget('replace_combo')->set_sensitive(false);
            $this->widget('replace_btn')->set_sensitive(false);
            $this->set_default($this->widget('search_btn'));
        }
        else
        {
            $this->widget('window')->set_default($this->widget('replace_btn'));
        }
        
        $this->show_all();
    }
    
    public function on_close_btn_clicked ()
    {
        $this->destroy();
    }
    
    public function on_search_btn_clicked ()
    {
        $app = Etk::get_app();
        $manager = $app->document_manager;
        $doc = $manager->get_document();
    }
}
