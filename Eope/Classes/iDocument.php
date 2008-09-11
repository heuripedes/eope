<?php

/**
 * iDocument.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

interface iDocument
{
    public function get_text ();
    public function set_text ($text);
    
    public function set_encoding ($encoding);
    public function get_encoding ();
    
    public function get_cursor_pos ();
    public function set_cursor_pos ($x, $y);
    
    public function set_filename ($filename);
    public function get_filename ();
	
	public function get_modified ();
	public function set_modified ($modified);
    
    public function load_file ($filename = false, $encoding = false);
    public function save_file ($filename = false, $encoding = false);
    
    public function get_buffer ();
    
    public function get_view ();
    
    public function get_widget ();
    
    public function set_focus ();
    
    public function copy_text ();
    public function paste_text ();
    public function cut_text ();
	
	public function undo ();
	public function redo ();
	public function can_redo ();
	public function can_undo ();
    
    public function connect_to_view   ($signal, $callback, $userdata = null);
    public function connect_to_buffer ($signal, $callback, $userdata = null);
    
    public function get_selection ();
    //public function set_selection ($x, $y, $x2, $y2); // might change
}
