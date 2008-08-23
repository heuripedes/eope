<?php

/**
 * Conf.php
 * 
 * This file is part of Etk
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

class EtkConf
{
    private $config = array();
    private $filename = null;
    
    public function __construct ($param = null, $file = false)
    {
        $this->merge($param, $file);
    }
    
    public function merge ($param = null, $file = false)
    {
        $config = array();
        if (is_array($param))
        {
            $config = $param;
        }
        elseif (is_string($param) && !$file)
        {
            $config = (array) unserialize($param);
        }
        elseif (is_string($param) && $file)
        {
            if (!file_exists($param))
            {
                throw new EtkException("Cannot open $file. No file or directory.");
            }
            printf("[%s] Loading configuration from '%s'\n", __CLASS__, $param);
            $config = parse_ini_file($param, true);
        }
        $this->config = array_merge($this->config, $config);
        
        return $this;
    }
    
    public function set ($n, $v = null)
    {
        $session = trim($n, '.');
        $property = false;
        
        if (strpos($n, '.'))
        {
            list($session, $property) = explode('.', $n);
        }
        
        if (!$session)
        {
            throw new EtkException ($session . 'is not a valid config session.');
        }
        if ($property)
        {
            $this->config[$session][$property] = $v;
        }
        else
        {
            $this->config[$session] = $v;
        }
        return $this;
    }
    
    public function get ($n)
    {
        $session = trim($n, '.');
        $property = false;
        
        if (strpos($n, '.'))
        {
            list($session, $property) = explode('.', $n);
        }
        
        if (!$session)
        {
            throw new EtkException ('%s is not a valid config session.', $session);
        }
        if ($property)
        {
            if (!isset($this->config[$session][$property]))
            {
                throw new EtkException ('%s.%s  is not a valid entry.', $session, $property);
            }
            
            return $this->config[$session][$property];
        }
        else
        {
            if (!isset($this->config[$session]))
            {
                throw new EtkException ('%s  is not a valid entry.', $session);
            }
            return $this->config[$session];
        }
    }
    
    public function store ($file = false)
    {
        if (!$file && $this->filename)
        {
            $file = $this->filename;
        }
        
        if (!$file)
        {
            throw new EtkException('configuration file not set.');
        }
        
        $out = '';
        foreach ($this->config as $key => $val)
        {
            if (is_array($val))
            {
                $out .= "[$key]\n";
                
                foreach ($val as $k=>$v)
                {
                    $out .= "$k=$v\n";
                }
            }
            else
            {
                $out .= "$key=$val\n";
            }
        }
        
        file_put_contents($file, $out);
    }
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
