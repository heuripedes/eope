<?php

/**
 * ConfigManager.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

class ConfigManager
{
    const SEPARATOR = '.';
    
    protected static $config = array(
        'eope' => array(
            'plugins' => 'DirectoryView:Pastebin'
            ),
        'ui' => array(
            'width' => 640,
            'height' => 480
            ),
        'editor' => array(
            'font' => 'monospace medium 9',
            'auto_indent' => true,
            'tab_style' => 4,
            'highlight_line' => true,
            'match_brackets' => true, 
            'smart_keys' => true,
            'word_wrap' => true,
            'line_numbers' => true,
            'line_markers' => true
            ),
        'files' => array(
            'reopen' => false,
            'last_files' => ''
            ),
        
        'side_panel' => array(
            'visible' => true,
            'width' => 100
            ),
        'bottom_panel' => array(
            'visible' => false,
            'height' => 100
            )
    );
    protected $modified = false;
    protected static $instance;
    
    protected function __construct ()
    {
        
    }
    
    public static function get_instance ()
    {
        if (!self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get ($config)
    {
        return $this->_get($config, self::$config);
    }
    
    public function set ($config, $val)
    {
        $this->modified = true;
        echo "[ConfigManager] Setting $config to ".substr($val, 0, 15)."...\n";
        return $this->_set($config, $val, self::$config);
    }
    
    public function load_from_file ($file)
    {
        $conf = parse_ini_file($file, true);
        if ($conf === false)
        {
            return;
        }
        self::$config = array_merge(self::$config, $conf);
    }
    
    public function load ()
    {
        if (file_exists(HOME_DIR . '.eope/eope.conf'))
        {
            echo "Loading configuration from " . HOME_DIR . ".eope/eope.conf\n";
            $this->load_from_file(HOME_DIR . '.eope/eope.conf', true);
        }
    }
    
    public function store ()
    {
        if (!$this->modified)
        {
            echo 'The configuration has not changed.';
            return;
        }
        
        if (!file_exists(HOME_DIR))
        {
            throw new EtkException('Cannot find user directory. The configuration will cannot be stored.');
        }
        
        if (!is_dir(HOME_DIR . '.eope'))
        {
            mkdir(HOME_DIR .'.eope');
        }
        
        $path = HOME_DIR .'.eope/';
        $fp = fopen($path . 'eope.conf', 'w');
        
        if (!(bool)$fp)
        {
            return;
        }
        
        $out = '';
        
        foreach (self::$config as $key => $val)
        {
            if (is_array($val))
            {
                $out .= "[$key]" . PHP_EOL;
                foreach ($val as $k=>$v)
                {
                    $out .= "$k=";
                    if (is_bool($v))
                    {
                        $out .= ($v ? 'true' : 'false');
                    }
                    else
                    {
                        $out .= $v;
                    }
                    $out .= PHP_EOL;
                }
            }
            else
            {
                $out .= "$key=$val" . PHP_EOL;
            }
        }
        
        echo "[ConfigManager] Storing the configuration in {$path}eope.conf\n";
        fwrite($fp, $out);
        fclose($fp);
    }
    
    protected function _get ($needle, $haystack)
    {
        if (strpos($needle, self::SEPARATOR))
        {
            $parts = explode(self::SEPARATOR, $needle);
            $needle = $parts[0];
        }
        else
        {
            if (isset($haystack[$needle]))
            {
                return $haystack[$needle];
            }
            return false;
        }
        
        if (isset($haystack[$needle]))
        {
            if (is_array($haystack[$needle]))
            {
                return $this->_get(implode(self::SEPARATOR, array_slice($parts, 1)), $haystack[$needle]);
            }
            return $haystack[$needle];
        }
        return false;
    }
    
    protected function _set ($needle, $val, &$haystack)
    {
        if (strpos($needle, self::SEPARATOR))
        {
            $parts = explode(self::SEPARATOR, $needle);
            $needle = $parts[0];
        }
        else
        {
            if (isset($haystack[$needle]))
            {
                $haystack[$needle] = $val;
                return true;
            }
            return false;
        }
        
        if (isset($haystack[$needle]))
        {
            if (is_array($haystack[$needle]))
            {
                return $this->_set(implode(self::SEPARATOR, array_slice($parts, 1)), $val, $haystack[$needle]);
            }
            $haystack[$needle] = $val;
            return true;
        }
        $haystack[$needle] = $val;
        return true;
    }
}

