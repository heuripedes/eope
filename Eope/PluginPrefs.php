<?php

/**
 * PluginPrefs.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

require_once(APP_DIR . 'PluginPrefsSignals.php');

class PluginPrefs extends EtkWindow
{
    protected $store = null;
    
    public function __construct ()
    {
        parent::__construct(APP_DIR . 'Glade/pluginwindow.glade','window');
        $this->connect_glade_to(new PluginListSignals($this));
        
        $treeview = $this->widget('treeview');
        
        $this->store = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_BOOLEAN);
        
        $text_renderer   = new GtkCellRendererText();
        $toogle_renderer = new GtkCellRendererToggle();
        
        $toogle_renderer->set_radio(false);
        $toogle_renderer->set_property('activatable', 1);
        $toogle_renderer->connect('toggled', array($this->signal_handler, 'on_toggle'), $this->store);
        
        $column1 = new GtkTreeViewColumn('Name', $text_renderer, 'text', 0);
        $column2 = new GtkTreeViewColumn('Enabled', $toogle_renderer, 'active', 1);
        
        $column1->set_expand(true);
        
        $column2->set_sizing(Gtk::TREE_VIEW_COLUMN_FIXED);
        $column2->set_fixed_width(100);

        $treeview->append_column($column1);
        $treeview->append_column($column2);
        
        $treeview->set_model($this->store);
        
        $this->populate_list();
        $this->refresh();
    }
    
    public function get_store ()
    {
        return $this->store;
    }
    
    public function populate_list ()
    {
        $treeview = $this->widget('treeview');
        $store = $this->store;
        $store->clear();
        
        $plugins = PluginManager::get_instance()->list_plugins();
        
        for ($i=0;$i < count($plugins); $i++)
        {
            $plugin = $plugins[$i];
            $store->append(array($plugin['name'], $plugin['loaded']));
        }
        $treeview->show_all();
    }
}
