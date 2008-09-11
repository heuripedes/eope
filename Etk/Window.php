<?php

abstract class EtkWindow
{
    protected $signal_handler;
    protected $glade;
    protected $accel_group;
    protected $window;
	protected $widgets;

    public function __construct ($gladefile = '', $widgetname = '', $root = FALSE)
    {
		
        if ($gladefile != '' && !file_exists($gladefile))
        {
            throw new EtkException('Glade file not found.');
        }
        
        if ($gladefile != '')
        {
            $this->glade = ($root ? new GladeXML($gladefile, $root) : new GladeXML($gladefile));
            $this->window = $this->glade->get_widget($widgetname);
            
            if (!$this->window instanceof GtkWindow)
            {
                throw new EtkException('The window was not found.');
            }
        }
        
        $this->accel_group = new GtkAccelGroup();
        $this->window->add_accel_group($this->accel_group);
		$this->widgets = array();
    }
    
    public function __destruct ()
    {
        $this->window->destroy();
    }

    public function set_window (GtkWindow $window)
    {
        $this->window = $window;
    }

    public function connect_glade_to ($class)
    {
        $this->signal_handler = $class;
        
        if ($class === null)
        {
            throw new EtkException('Cannot assign signal handler: null given.');
        }
        
        $this->glade->signal_autoconnect_instance($class);
    }
    
    public function connect_glade ()
    {
        $this->glade->signal_autoconnect_instance($this);
    }
	
	public function add_widget ($name, $widget)
	{
		$this->widgets[$name] = $widget;
	}

    public function widget ($name)
    {
		if (array_key_exists($name, $this->widgets))
		{
			return $this->widgets[$name];
		}
		
        if (!$this->glade instanceof GladeXML)
        {
            throw new EtkException(_("Cannot find the widget '%s'."), $name);
        }

        $widget = $this->glade->get_widget($name);

        return $widget;
    }
    
    public function get_accel_group ()
    {
        return $this->accel_group;
    }

    public function refresh ()
    {
        $this->window->show();
    }

    public function __call ($method, $params)
    {
        if (!method_exists($this, $method) && $this->window instanceof GtkWindow)
        {
            if (!method_exists($this->window, $method))
            {
                throw new EtkException('The method '.$method.' does not exists.');
            }
            return call_user_func_array(array($this->window, $method), $params);
        }
        //return call_user_func_array(array($this, $method), $params);
    }
    
    public function get_window ()
    {
        return $this->window;
    }
}
