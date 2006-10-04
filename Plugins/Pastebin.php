<?php

class PastebinPlugin extends Plugin
{
	protected $menu = null;
	protected $window = null;
	
	public function __construct ()
	{
	}
	
	public function get_handled_events ()
	{
		return array('main_window_create', 'activate_widgets');
	}
	
	public function on_main_window_create ($window)
	{
		
		//$window = $args[0];
		$this->window = $window;
		$git = GtkIconTheme::get_default();
        $icon = $git->load_icon('gtk-paste', 16, Gtk::ICON_LOOKUP_USE_BUILTIN);
		
		$this->menu = new GtkImageMenuItem('php.Pastebin.com');
        $this->menu->set_image(GtkImage::new_from_pixbuf($icon));
        $this->menu->connect_simple('activate', array($this, '_on_menu_activate'));
        
        $accel = $window->get_accel_group();
		$this->menu->add_accelerator('activate', $accel, ord('p'),
			Gdk::CONTROL_MASK | Gdk::SHIFT_MASK, Gtk::ACCEL_VISIBLE);
	
		$window->widget('tools_menu_menu')->add($this->menu);
		$window->widget('tools_menu_menu')->reorder_child($this->menu, 0);
		$window->widget('tools_menu_menu')->show_all();
	}
	
	public function on_activate_widgets ($active)
	{
		$this->menu->set_sensitive($active);
	}
	
	public function _on_menu_activate ()
	{
		$document = $this->window->document_manager->get_document();
		
		if ($document === false)
		{
			return;
		}
		
		$body ="parent_pid=&format=php";
		$body .= "&code2=".$document->get_text();
		$body .= "&poster=Eope&paste=Send&remember=1&expiry=m&email=";
		
		$request = "POST /pastebin.php HTTP/1.0\r\n";
		$request .= "Host: php.pastebin.com\r\n";
		$request .= "Accept: */*;q=0.1\r\n";
		$request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$request .= "Content-length: " . strlen($body). "\r\n\r\n";
		$request .= $body;
		$request .= "\r\n";
		
		$fp = fsockopen('php.pastebin.com', 80, $errno, $errstr, 30);
		
		$title = 'Error';
		$message = "Cannot paste your code.\n$errno: $errstr\n";
		$type = Gtk::MESSAGE_ERROR;
		
		if ($fp !== false)
		{
			fwrite($fp, $request);

			$response = '';
			while (!feof($fp))
			{
				$response .= fread($fp, 1024);
			}
			fclose($fp);

			preg_match('/Location: http:\/\/php.pastebin.com\/([a-z0-9]+)/', $response, $results);
			
			if (isset($results[1]))
			{
				$title = 'Paste sucessful';
				$message = "Your code has been pasted. Check it at:\n\nhttp://php.pastebin.com/".$results[1];
				$type = Gtk::MESSAGE_INFO;
			}
			else
			{
				$message = "Cannot paste your code.";
			}
		}
		
		$dialog = new GtkMessageDialog (
			$this->window->get_window(),
			Gtk::DIALOG_MODAL, 
			$type,
			Gtk::BUTTONS_OK,
			$message
		);
		$dialog->set_title($title);
		$dialog->run();
		$dialog->destroy();
	}
}