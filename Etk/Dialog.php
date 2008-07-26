<?php

/**
 * Dialog.php
 * 
 * Class to easy create basic dialogs.
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

class EtkDialog 
{
	protected static function get_window ($window = null)
	{
		if ($window === null)
		{
			$window = Etk::get_app()->get_window();
		}
		if ($window instanceof EtkWindow)
		{
			$window = $window->get_window();
		}
		if (!$window instanceof GtkWindow && $window !== null)
		{
			throw new EtkException('$window is not a instance of GtkWindow.');
		}
		return $window;
	}
	
	public static function save_as ($window = null)
	{
		$window = self::get_window($window);
		
		$dialog = new GtkFileChooserDialog('Save as', $window, Gtk::FILE_CHOOSER_ACTION_SAVE,
			array(Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL, Gtk::STOCK_SAVE, Gtk::RESPONSE_ACCEPT), null);
		$dialog->show_all();

		if ($dialog->run() != Gtk::RESPONSE_ACCEPT)
		{
			$dialog->destroy();
			return false;
		}
		$filename = $dialog->get_filename();

		$dialog->destroy();
		
		return $filename;
	}
	
	public static function open_file ($window = null)
	{
		$window = self::get_window($window);
		$dialog = new GtkFileChooserDialog('Open file', $window, Gtk::FILE_CHOOSER_ACTION_OPEN,
            array(Gtk::STOCK_OK, Gtk::RESPONSE_OK), null);
        $dialog->show_all();

        if ($dialog->run() != Gtk::RESPONSE_OK)
        {
        	$dialog->destroy();
        	return false;
        }
		$filename = $dialog->get_filename();
		
        $dialog->destroy();
        
        return $filename;
	}
	
	public static function open_dir ($window = null)
	{
		$window = self::get_window($window);
		
		$dialog = new GtkFileChooserDialog("Open directory", $window,
            Gtk::FILE_CHOOSER_ACTION_SELECT_FOLDER,
            array(Gtk::STOCK_OK, Gtk::RESPONSE_OK), null);
        $dialog->show_all();

        if ($dialog->run() != Gtk::RESPONSE_OK)
        {
        	$dialog->destroy();
        	return false;
            
        }
        $dirpath = $dialog->get_filename();
        $dialog->destroy();
        return $dirpath;
	}
	
}

