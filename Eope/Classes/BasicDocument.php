<?php

/**
 * BasicDocument.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

require_once APP_DIR . 'Classes/DocumentAbstract.php';

class Document extends DocumentAbstract
{
	public function __construct ()
	{
		parent::__construct ();
		$this->buffer = new GtkTextBuffer();
		$this->view   = new GtkTextView($this->buffer);
	}
	
	public function get_text ()
	{
		return $this->buffer->get_text($this->buffer->get_start_iter(), $this->buffer->get_end_iter());
	}
	
    public function set_text ($text)
	{
		$this->buffer->set_text($text);
	}
	
	public function get_widget ()
    {
        return $this->view;
    }
	
	public function get_modified ()
	{
		return $this->view->get_modified();
	}
	
	public function set_modified ($modified)
	{
		$this->view->set_modified();
	}
    
    public function set_focus ()
    {
        $this->view->grab_focus();
    }
	
	public function get_cursor_pos ()
    {
		$pos = new stdclass;
		$pos->pos = $this->view->get_current_pos();
		$pos->line = $this->view->line_from_position($pos->pos);
		$pos->column = $this->view->get_column($pos->pos);
		return $pos;
    }
    
    public function set_cursor_pos ($x, $y)
    {
    }
    
    public function copy_text ()
    {
		$this->view->copy();
    }
    
    public function paste_text ()
    {
		$this->view->paste();
    }
    
    public function cut_text ()
    {
		$this->view->cut();
    }
	
	public function undo ()
	{
		$this->view->undo();
	}
	
	public function redo ()
	{
		$this->view->redo();
	}
	
	public function can_redo ()
	{
		$this->view->can_redo();
	}
	
	public function can_undo ()
	{
		$this->view->can_undo();
	}
    
    public function get_selection ()
    {
	}
}
