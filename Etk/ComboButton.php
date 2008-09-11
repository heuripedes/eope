<?php

class EtkComboButton extends GtkToggleButton
{
	private $arrow;
	private $hbox;
	private $label;
	private $menu;
	private $biggest = 0;
	private $active_item;
	public  $__gsignals = array(
		'changed' => array(GObject::SIGNAL_RUN_FIRST, GObject::TYPE_NONE, array(GObject::TYPE_OBJECT, GObject::TYPE_LONG))
	);
	
	public function __construct ($with_separator = false)
	{
		parent::__construct();
		
		$this->hbox  = new GtkHBox(false, 6);
		$this->label = new GtkLabel();
		$this->arrow = new GtkArrow(Gtk::ARROW_DOWN, gtk::SHADOW_NONE);
		$this->menu  = new GtkMenu();
		
		$this->label->set_alignment(0, 0);
		
		$this->label->set_justify(Gtk::JUSTIFY_LEFT);
		
		$this->hbox->pack_start($this->label);
		
		if ($with_separator)
		{
			$this->hbox->pack_start(new GtkVSeparator(), false, true);
		}
		
		$this->hbox->pack_start($this->arrow, false, true);
		
		$this->add($this->hbox);
		
		$this->set_focus_on_click(false);
		$this->set_property('can-focus', false);
		
		$this->connect('button-press-event', array($this,'on_toggle'));
        $this->menu->connect('deactivate', array($this, 'on_close'));
	}
	
	public function set_text ($text)
	{
		$this->label->set_text($text);
	}
	
	public function append ($item)
	{
		$this->menu->append($item);
		$children = $item->get_children();
		$label = null;
		
		foreach ($children as $child)
		{
			if ($child instanceof GtkContainer)
			{
				$children2 = $child->get_children();
				
				foreach ($children2 as $child2)
				{
					if ($child2 instanceof GtkLabel)
					{
						$label = $child2;
						break 2;
					}
				}
			}
			elseif ($child instanceof GtkLabel)
			{
				$label = $child;
				break;
			}
		}
		
		if (!$label instanceof GtkLabel)
		{
			return false;
		}
		
		$len = $label->get_width_chars();
		
		if ($len < 0)
		{
			$len = strlen($label->get_text());
		}
		
		if ($this->label->get_text() == '')
		{
			$this->set_text($label->get_text());
			$this->active_index = 0;
		}
		
		if ($len > $this->biggest)
		{
			$this->biggest = $len;
			$this->label->set_width_chars($this->biggest);
		}
	}
	
	public function set_active_index ($index)
	{
		$children = $this->menu->get_children();
		$len = count($children);
		for ($i = 0 ; $i < $len; $i++)
		{
			if ($i == $index)
			{
				$this->menu->activate_item($children[$i], false);
				$this->active_index = $i;
			}
		}
	}
	
	private function get_item ($idx)
	{
		$children = $this->menu->get_children();
		$len = count($children);
		for ($i = 0 ; $i < $len; $i++)
		{
			if ($i == $idx)
			{
				return $children[$i];
			}
		}
	}
	
	private function get_index ($item)
	{
		$children = $this->menu->get_children();
		$len = count($children);

		for ($i = 0; $i < $len; $i++)
		{
			if ($children[$i] === $item)
			{
				return $i;
			}
		}
	}
	
	public function get_active_index ()
	{
		return $this->active_index;
	}
	
	public function get_active_text ()
	{
		return trim($this->get_item($this->active_index)->get_text());
	}
	
	public function append_text ($text)
	{
		$item = new GtkMenuItem($text);
		$item->show();
		$this->append($item);
		$item->connect_simple('activate', array($this, 'on_item_activate'), $item);
		return $item;
	}
	
	public function menu_position ()
	{
		$btn_rect     = $this->get_allocation();
		$menu_req = $this->menu->size_request();
		
		$items = count($this->menu->get_children());
		
		list($win_x, $win_y) = $this->window->get_origin();
		list($win_w, $win_h) = $this->window->get_size();
		
		$x = $btn_rect->x + $win_x;
		$y = $btn_rect->y + round($btn_rect->height/2) + $win_y;
		
		if (($y + $menu_req->height) > ($win_y + $win_h))
		{
			$y -= $menu_req->height;
		}
        
        return array($x, $y, false);
	}
	
	public function on_toggle ($widget, $ev)
	{
		if ($ev->button == 1)
		{
            $this->set_active(true);
            $this->menu->popup(null, null, array($this, 'menu_position'), $ev->button, $ev->time);
		}
	}
	
	public function on_close ()
	{
		$this->set_active(false);
	}
	
	public function on_item_activate ($item)
	{
		$this->set_text($item->get_child()->get_text());
		$this->active_index = $this->get_index($this->menu->get_active());
		$this->emit('changed', $this, $this->active_index);
	}
}

GObject::register_type('EtkComboButton');
