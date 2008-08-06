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
    const INFO = Gtk::MESSAGE_INFO;
    const WARN = Gtk::MESSAGE_WARNING;
    const ERROR = Gtk::MESSAGE_ERROR;
    const QUESTION = Gtk::MESSAGE_QUESTION;
    const CONFIRM = Gtk::MESSAGE_QUESTION;
    
    private static $file_dialog_opts = array(
        Gtk::STOCK_CANCEL,
        Gtk::RESPONSE_CANCEL,
        Gtk::STOCK_SAVE,
        Gtk::RESPONSE_ACCEPT
    );

    private static $valid_types = array(
        self::INFO, self::WARN, self::ERROR, self::QUESTION, self::CONFIRM
    );

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
            throw new EtkException('argument 1 of %s::%s must be eiter a instance '.
                                   'of EtkWindow or GtkWindow',__CLASS__,__FUNCTION__);
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
            array(Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL, Gtk::STOCK_OPEN, Gtk::RESPONSE_ACCEPT), null);
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
    
    public static function open_dir ($window = null)
    {
        $window = self::get_window($window);
        
        $dialog = new GtkFileChooserDialog('Open directory', $window,
            Gtk::FILE_CHOOSER_ACTION_SELECT_FOLDER,
            array(Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL, Gtk::STOCK_OPEN, Gtk::RESPONSE_ACCEPT), null);
        $dialog->show_all();

        if ($dialog->run() != Gtk::RESPONSE_ACCEPT)
        {
            $dialog->destroy();
            return false;
            
        }
        $dirpath = $dialog->get_filename();
        $dialog->destroy();
        return $dirpath;
    }
    
    public static function message ($message, $title = null, $type = EtkDialog::INFO, $window = null, $flags = 0)
    {
        $window = self::get_window($window);
        
        if (!in_array($type, self::$valid_types))
        {
            $type = self::INFO;
        }
        
        // TODO: add self::CONFIRM dialog :)
        if($type == self::QUESTION)
        {
            $win = new GtkMessageDialog($window, $flags, $type, Gtk::BUTTONS_YES_NO, '');
        }
        else
        {
            $win = new GtkMessageDialog($window, $flags, $type, Gtk::BUTTONS_CLOSE, '');
        }
        
        $win->set_position(Gtk::WIN_POS_CENTER);
        $win->set_title($title);
        $win->set_markup($message);
        
        $response = $win->run();
        $win->destroy();
        
        return $response;
    }
}

