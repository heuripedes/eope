<?php

/**
 * DocumentAbstract.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

require_once APP_DIR . 'Classes/iDocument.php';

abstract class DocumentAbstract extends GObject implements iDocument
{
    const E_CANT_WRITE    =  1;
    const E_IS_DIR        =  2;
    const E_CANNOT_OPEN   =  4;
    const E_NO_SUCH_FILE  =  8;
    const E_UNREADABLE    = 16;
    
    protected $view;
    protected $buffer;
    protected $swindow;
    protected $encoding;
    protected $file_name;
    protected $file_mtime;
    protected $file_last_mtime;
    
    public $__gsignals = array(
        'ui-update'     => array(GObject::SIGNAL_RUN_FIRST, GObject::TYPE_NONE, array(GObject::TYPE_OBJECT)),
        'change-fold'   => array(GObject::SIGNAL_RUN_FIRST, GObject::TYPE_NONE, array(GObject::TYPE_LONG, GObject::TYPE_LONG, GObject::TYPE_LONG)),
        'insert'        => array(GObject::SIGNAL_RUN_FIRST, GObject::TYPE_NONE, array(GObject::TYPE_LONG, GObject::TYPE_STRING)),
        'delete'        => array(GObject::SIGNAL_RUN_FIRST, GObject::TYPE_NONE, array(GObject::TYPE_LONG)),
        'key-press'     => array(GObject::SIGNAL_RUN_FIRST, GObject::TYPE_NONE, array()),
        'key-release'   => array(GObject::SIGNAL_RUN_FIRST, GObject::TYPE_NONE, array()),
        'mouse-press'   => array(GObject::SIGNAL_RUN_FIRST, GObject::TYPE_NONE, array()),
        'mouse-release' => array(GObject::SIGNAL_RUN_FIRST, GObject::TYPE_NONE, array())
    );
    
    public function __construct ()
    {
        parent::__construct();
        
        if (HAS_MBSTRING)
        {
            mb_detect_order('UTF-8,ISO-8859-1,ASCII,UTF-7,EUC-JP,SJIS,eucJP-win,SJIS-win,JIS,ISO-2022-JP');
        }
        
        $this->swindow = new GtkScrolledWindow ();
        $this->swindow->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
        
    }
    
    public function set_encoding ($encoding)
    {
        $this->encoding = $encoding;
    }
    
    public function get_encoding ()
    {
        return $this->encoding;
    }
    
    public function set_filename ($filename)
    {
        $this->file_name = $filename;
    }
    
    public function get_filename ()
    {
        return $this->file_name;
    }
    
    public function load_file ($filename = false, $encoding = false)
    {
        if ($filename === false)
        {
            $filename = $this->file_name;
        }
        
        if (!file_exists($filename))
        {
            return self::E_NO_SUCH_FILE;
        }
        
        if (!is_readable($filename))
        {
            return self::E_UNREADABLE;
        }
        
        $fp = fopen($filename, 'rb');
        
        if (!$fp)
        {
            return self::E_CANNOT_OPEN;
        }
        
        $text = fread($fp, filesize($filename));
        fclose($fp);
        
        if (HAS_MBSTRING)
        {
            if ($encoding)
            {
                $from = $encoding;
            }
            else
            {
                $from = mb_detect_encoding($text);
            }
            //$text = mb_convert_encoding($text, , $from);
            //$text = mb_convert_encoding($text, ini_get('php-gtk.codepage'), $from);
            $text = mb_convert_encoding($text, ini_get('php-gtk.codepage'), $from);
        }
        
        if (HAS_MBSTRING)
        {
            $this->encoding = $from;
        }
        else
        {
            $this->encoding = $encoding;
        }

        $this->set_text($text);
        $this->file_name = $filename;
        
        return true;
    }
    
    public function save_file ($filename = false, $encoding = false)
    {
        $path = ($filename == false ? $this->file_name : $filename);
        
        if ((file_exists($path) && !is_writable($path)))
        {
            return self::E_CANT_WRITE;
        }
        if (is_dir($path))
        {
            return self::E_IS_DIR;
        }
        
        $fp   = fopen($path, 'wb');
        
        if (!$fp)
        {
            return self::E_CANNOT_OPEN;
        }
        
        $text = $this->get_text();
        
        if (HAS_MBSTRING)
        {
            $encoding = ($encoding == false ? $this->encoding  : $encoding);
        	$text = mb_convert_encoding($text, $encoding, ini_get('php-gtk.codepage'));
        }
        
        fwrite($fp, $text, strlen($text));
        fclose($fp);
        
        return true;
    }

    public function connect_to_view ($signal, $callback, $userdata = null)
    {
        $this->view->connect($signal, $callback, $userdata);
    }
    
    public function connect_to_buffer ($signal, $callback, $userdata = null)
    {
        $this->buffer->connect($signal, $callback, $userdata);
    }

    public function get_buffer ()
    {
        return $this->buffer;
    }
    
    public function get_view ()
    {
        return $this->view;
    }
    
    
}

GObject::register_type('DocumentAbstract');
//GObject::register_type('Document');




