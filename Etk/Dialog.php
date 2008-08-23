<?php

/**
 * Dialog.php
 * 
 * This file is part of Etk
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

/**
 * EtkDialog
 * 
 * This class manages the dialog windows creation.
 * 
 * @author Higor "enygmata" Eurípedes
 */
class EtkDialog 
{
    const YESNO       = 'yesno';
    const YESNOCANCEL = 'yesnocancel';
    const INFO        = 'info';
    const WARN        = 'warning';
    const ERROR       = 'error';
    const QUESTION    = 'question';
    
    protected static $file_dialog_opts = array(
        Gtk::STOCK_CANCEL,
        Gtk::RESPONSE_CANCEL,
        Gtk::STOCK_SAVE,
        Gtk::RESPONSE_ACCEPT
    );

    protected static $gtk_dialog_types = array(
        'yesno'       => GTK::MESSAGE_QUESTION,
        'yesnocancel' => GTK::MESSAGE_QUESTION,
        'error'          => GTK::MESSAGE_ERROR,
        'warning'     => GTK::MESSAGE_WARNING,
        'info'        => GTK::MESSAGE_INFO
    );
    
    protected static $gtk_dialog_buttons = array(
        'yesno'       => Gtk::BUTTONS_YES_NO,
        'yesnocancel' => Gtk::BUTTONS_YES_NO,
        'error'       => Gtk::BUTTONS_CLOSE,
        'warning'     => Gtk::BUTTONS_CLOSE,
        'info'        => Gtk::BUTTONS_CLOSE
    );
    
    /**
     * Returns the GtkWindow from the specified variable.
     * 
     * The function will try to autodetect and return a GtkWindow
     * from the given parameter.
     * 
     * The function will throw an EtkException if the GtkWindow cannot be found.
     * 
     * @param mixed $window a GtkWindow, EtkWindow or NULL to get the main application window.
     * @return GtkWindow a GtkWindow.
     */

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
    
    /**
     * Displays a 'Save as' dialog.
     * 
     * The function displays a 'Save file as' dialog and returns the user response.
     * 
     * @param mixed $window the parent window.
     * @return int the user response
     * @see EtkDialog::get_window()
     */
    
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
    
    /**
     * Displays a 'Open file' dialog
     * 
     * This function will attempt to display a 'Open file' dialog and return
     * the user response.
     * 
     * @param mixed $window the parent window.
     * @return int the user response
     * @see EtkDialog::get_window(), EtkDialog::open_dir()
     */
    
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
    
    /**
     * Displays a 'Open directory' window.
     * 
     * This function attempts to display a 'Open directory' dialog (Windows users know this
     * as 'Select directory') and return the user response
     * 
     * @param mixed $window the parent window.
     * @return int the user response
     * @see EtkDialog::get_window(), EtkDialog::open_file()
     */
    
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
    
    /**
     * Displays a message to the user.
     * 
     * This function attempts to display a information/warning/error/confirmation/question
     * message to the user and returns his response.
     * @param string $message the message to be displayed.
     * @param string $title the dialog title.
     * @param string $type the type of the dialog.
     * @param mixed $window the parent window.
     * @param int $flags GTK_DIALOG_FLAGS to be given during the dialog creation.
     * @return int the user response
     * @see EtkDialog::get_window(), EtkDialog::open_file()
     */
    
    public static function message ($message, $title, $type = EtkDialog::INFO, $window = null, $flags = 0)
    {
        $window = self::get_window($window);
        
        if (!isset(self::$gtk_dialog_types[$type]))
        {
            throw new EtkException("'%s' is not a valid dialog type", $type);
        }
        
        $gtktype = self::$gtk_dialog_types[$type];
        $buttons = self::$gtk_dialog_buttons[$type];
               
        $win = new GtkMessageDialog($window, $flags, $gtktype, $buttons, $message);
        
        if ($type == self::YESNOCANCEL)
        {
            $win->add_buttons(array(Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL));
        }
        
        $win->set_position(Gtk::WIN_POS_CENTER);
        $win->set_title($title);
        
        $response = $win->run();
        $win->destroy();
        
        return $response;
    }
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
