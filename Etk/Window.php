<?php

require_once('Etk/SignalHandler.php');

abstract class EtkWindow extends EtkObject
{
    protected $signal_handler = null;
    protected $window = null;
    protected $glade = null;
    protected $glade_mode = false;
    protected $glade_file = '';

    public function __construct (EtkApplication $application)
    {
        $this->application = $application;
    }

    public static function new_raw (EtkApplication $application, $title = 'EtkWindow',
        $position = Etk::WIN_POS_CENTER, $type = Etk::WINDOW_TOPLEVEL)
    {
        $etkw = new self($application);
        $etkw->create_raw($title, $position, $type);
        return $etkw;
    }

    public static function new_from_glade (EtkApplication $application, $gladefile, $widgetname)
    {
        $etkw = new self($application);
        $etkw->create_from_glade($gladefile, $widgetname);
        return $etkw;
    }

    public static function new_from_buffer (EtkApplication $application, $buffer, $widgetname)
    {
        $etkw = new self($application);
        $etkw->create_from_buffer($buffer, $widgetname);
        return $etkw;
    }

    public function create_raw ($title = 'EtkWindow', $position = Etk::WIN_POS_CENTER,
        $type = Etk::WINDOW_TOPLEVEL)
    {
        $this->window = new GtkWindow($type);
        $this->window->set_title($title);
        $this->window->set_position($position);
    }

    public function create_from_glade ($gladefile, $widgetname)
    {
        if (!class_exists('GladeXML'))
        {
            Etk::FatalError(__CLASS__, 'Glade extension is not loaded.');
        }

        $this->glade = new GladeXML($gladefile);//, $widgetname);
        $this->window = $this->glade->get_widget($widgetname);

        if (!$this->window instanceof GtkWindow)
        {
            Etk::Warn(__CLASS__, 'Window not found.');
        }
        $this->glade_mode = true;
        $this->glade_file = $gladefile;
    }

    public function create_from_buffer ($gladebuffer, $widgetname)
    {
        if (!class_exists('GladeXML'))
        {
            Etk::FatalError(__CLASS__, 'Glade extension is not loaded.');
        }
        $this->glade = GladeXML::new_from_buffer($gladebuffer);
        $this->window = $this->glade->get_widget($widgetname);

        if (!$this->window instanceof GtkWindow)
        {
            Etk::Error(__CLASS__, 'Window not found.');
        }
        $this->glade_mode = true;
    }

    public function set_signal_handler (EtkSignalHandler $handler)
    {
        $this->signal_handler = $handler;
    }

    public function auto_signal_handler ($classname)
    {
        if (class_exists($classname.'Signals'))
        {
            $classname .= 'Signals';
            $this->signal_handler = new $classname($this->application, $this);
        }
    }

    public function auto_connect ()
    {
        if (!$this->signal_handler instanceof EtkSignalHandler)
        {
            Etk::FatalError(__CLASS__, 'Signal handler not set.');
        }

        if (!$this->glade_mode)
        {
            Etk::Warn(__CLASS__, 'Auto signal connection is not implemented for non-glade mode yet.');
            return;
        }

        $this->glade->signal_autoconnect_instance($this->signal_handler);
    }

    public function gtk_window ()
    {
        return $this->window;
    }

    public function widget ($widgetname)
    {
        if (!$this->glade_mode)
        {
            Etk::Trace(__CLASS__, 'Widget access for raw window mode is not implemented yet.');
            return;
        }

        $widget = $this->glade->get_widget($widgetname);

        if ($widget instanceof GtkWidget)
        {
            return $widget;
        }

        Etk::Error(__CLASS__, 'Widget "'.$widgetname.'" not found.');
    }


    public function refresh ()
    {
        $this->window->show();
    }

    public function __call ($method, $params)
    {
        if (!method_exists($this, $method) && $this->window instanceof GtkWindow)
        {
            return call_user_func_array(array($this->window, $method), $params);
        }
        //return call_user_func_array(array($this, $method), $params);
    }
}
