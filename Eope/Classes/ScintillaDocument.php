<?php

/**
 * ScintillaDocument.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

require_once APP_DIR . 'Classes/DocumentAbstract.php';
require_once APP_DIR . 'Classes/Scintilla/SciBase.php';

// TODO: remove this testing stuff.. and add configuration reading for ScintillaDocument class
// FIXME: fix highlighting for open/end php tags

class Document extends DocumentAbstract
{
    public function __construct ()
    {
        parent::__construct();
        
        $this->view = new GtkScintilla();
        
        $this->view->set_code_page(SC_CP_UTF8);
        
		$this->view->connect('margin_click', array($this, 'on_margin_click'));
		$this->view->connect('update_ui',    array($this, 'on_update_ui'));
		$this->view->connect('char_added',   array($this, 'on_char_added'));
        
        $this->view->style_set_font(STYLE_DEFAULT, '!Liberation Mono');
        $this->view->style_set_size(STYLE_DEFAULT, 10);
        
		// setup the fold markers
		$this->view->marker_define(SC_MARKNUM_FOLDER,        SC_MARK_ARROW);
		$this->view->marker_define(SC_MARKNUM_FOLDEROPEN,    SC_MARK_ARROWDOWN);
		$this->view->marker_define(SC_MARKNUM_FOLDEREND,     SC_MARK_EMPTY);
		$this->view->marker_define(SC_MARKNUM_FOLDERTAIL,    SC_MARK_EMPTY);
		$this->view->marker_define(SC_MARKNUM_FOLDEROPENMID, SC_MARK_EMPTY);
		$this->view->marker_define(SC_MARKNUM_FOLDERMIDTAIL, SC_MARK_EMPTY);
		$this->view->marker_define(SC_MARKNUM_FOLDERSUB,     SC_MARK_EMPTY);
		
	
		$this->view->marker_set_back(SC_MARKNUM_FOLDEREND,     0);
		$this->view->marker_set_back(SC_MARKNUM_FOLDERMIDTAIL, 0);
		$this->view->marker_set_back(SC_MARKNUM_FOLDERTAIL,    0);
		$this->view->marker_set_back(SC_MARKNUM_FOLDERSUB,     0);
		$this->view->marker_set_back(SC_MARKNUM_FOLDER,    0);
		
		// setup margins
		$this->view->style_set_back(STYLE_LINENUMBER, 0xe2e2e2);
		$this->view->style_set_back(SC_MARGIN_BACK,   0xe2e2e2);
		
		$this->view->set_margin_width_n(0, 40);
        $this->view->set_margin_width_n(1,  0);
        $this->view->set_margin_width_n(2, 15);
		
		$this->view->set_caret_line_visible(true);
		$this->view->set_caret_line_back(invert(0x0000ff));
		$this->view->set_caret_line_back_alpha(20);
		
		$this->view->set_margin_mask_n(0, SC_MARGIN_SYMBOL);
		$this->view->set_margin_mask_n(0, SC_MARGIN_NUMBER);
		$this->view->set_margin_mask_n(2, SC_MASK_FOLDERS);
		
		$this->view->set_fold_margin_colour(true, 0xe2e2e2);
		$this->view->set_fold_margin_hi_colour(true, 0xe2e2e2);
		
		
		$this->view->set_margin_sensitive_n (0, true);
		$this->view->set_margin_sensitive_n (1, true);
		$this->view->set_margin_sensitive_n (2, true);
		
		$this->view->set_property('fold',              '1');
		$this->view->set_property('fold.compact',      '0');
		$this->view->set_property('fold.comment',      '1');
		$this->view->set_property('fold.preprocessor', '1');
		$this->view->set_property('fold.at.else',      '1');
		
		$this->view->set_indentation_guides(true);
		$this->view->set_backspace_unindents(true);
		$this->view->set_indent(4);
		$this->view->set_use_tabs(false);
		
		// TODO: delete the 2 following lines when i implement a language detection system
        $hx = SciHighlightFactory::get_highlighter('PHP', $this->view);
        $hx->setup_highlight();
    }
    
    public function on_modified_signal ($sci, $pos, $type, $length, $lines_added, $text, $line, $curr_fold_level, $prev_fold_level)
    {
        if (0) 0;
        else
		if ($type & SC_MOD_INSERTTEXT)
        {
            $this->emit('insert', $pos, $text);
        }
        elseif ($type & SC_MOD_DELETETEXT)
        {
			$this->on_delete($pos);
            $this->emit('delete', $pos);
        }
        elseif ($type & SC_MOD_CHANGEFOLD)
        {
            $this->emit('change-fold', $line, $curr_fold_level, $prev_fold_level);
        }
	}
	
	public function on_update_ui ()
	{
		$this->emit('ui-update', $this);
	}
	
	public function on_margin_click ($sci, $mod, $pos, $margin)
	{
		$line = $this->view->line_from_position($pos);
		
		//echo "Margin: $margin\n";
		
		switch($margin)
		{
			case 0:
			case 1: 
				$set = ($this->view->marker_get($line)) & (1 << SC_MARK_CIRCLE);
				if (!$set)
				{
					$this->view->marker_add($line, SC_MARK_CIRCLE);
				}
				else
				{
					$this->view->marker_delete($line, SC_MARK_CIRCLE);
				}
				break;
			case 2: $this->on_fold_click($pos); break;
		}
	}
	
	public function on_fold_click ($pos)
	{
		$line = $this->view->line_from_position($pos);
		$this->view->toggle_fold($line);
	}
	
	public function on_char_added ($sci, $ch)
	{
		switch (chr($ch))
		{
			case "\n": $this->on_new_line(); break;
			case '[':
			case '(':
			case '{': $this->on_brace_insert(chr($ch)); break;
		}
	}
	
	public function on_delete ($pos)
	{
		
	}
	
	public function on_new_line ()
	{
		$pos  = $this->view->get_current_pos();
		$line = $this->view->line_from_position($pos);
		
		$indent = $this->view->get_line_indentation($line-1);
		$this->view->set_line_indentation($line, $indent);
		$this->view->goto_pos($this->view->get_line_indent_position($line));
	}
	
	public function on_brace_insert ($brace)
	{
		$pos  = $this->view->get_current_pos();
		
		switch($brace)
		{
			case '{': $this->view->add_text('}', 1); break;
			case '(': $this->view->add_text(')', 1); break;
			case '[': $this->view->add_text(']', 1); break;
		}
		$this->view->goto_pos($pos);
	}
    
    public function load_file ($filename = false, $encoding = false)
    {
        $r = parent::load_file($filename, $encoding);
        $this->view->empty_undo_buffer();
        return $r;
    }
    
    public function get_widget ()
    {
        return $this->view;
    }
	
	public function get_modified ()
	{
		return $this->view->get_modify();
	}
	
	public function set_modified ($modified)
	{
		if (!$modified)
		{
			$this->view->empty_undo_buffer();	
			$this->view->set_save_point();
		}
	}
    
    public function set_focus ()
    {
        $this->view->grab_focus();
    }
    
    public function get_text ()
    {
        return $this->view->get_text();
    }
    
    public function set_text ($text)
    {
        $this->view->set_text($text);
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




