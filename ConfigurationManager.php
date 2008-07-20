<?php

class ConfigurationManager extends EtkObject
{
	const SEPARATOR = '.';
	
	protected static $config = array(
		'ui' => array(
			'width' => 640,
			'height' => 480
			),
		'editor' => array(
			'font' => 'monospace medium 9',
			'auto_indent' => true,
			'tab_style' => 4,
			'highlight_line' => false,
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
		'plugins' => '',
		'side_panel' => array(
			'visible' => false,
			'width' => 100
			),
		'bottom_panel' => array(
			'visible' => false,
			'height' => 100
			)
	);
	protected $modified = false;
	
	public function __construct ()
	{
		$this->set_name('ConfigurationManager');
	}
	
	public function get ($config)
	{
		return $this->_get($config, self::$config);
	}
	
	public function set($config, $val)
	{
		$this->modified = true;
		Etk::Trace(__CLASS__, "Changing $config to $val");
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
		$userdir = EtkOS::get_profile();

		if (file_exists($userdir . '/.eope/eope.conf'))
		{
			Etk::Trace(__CLASS__, 'Loading configuration from '.$userdir . '/.eope/eope.conf');
			$this->load_from_file($userdir . '/.eope/eope.conf', true);
		}
	}
	
	public function store ()
	{
		if (!$this->modified)
		{
			Etk::Error(__CLASS__, 'The configuration has not changed.');
			return;
		}
		$userdir = EtkOS::get_profile();
		
		if (!file_exists($userdir))
		{
			Etk::Error(__CLASS__, 'Cannot find user directory. The configuration will cannot be stored.');
			return;
		}
		
		if (!is_dir($userdir . '/.eope'))
		{
			mkdir($userdir .'/.eope');
		}
		$path = $userdir .'/.eope/';
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
		
		Etk::Trace(__CLASS__, 'Storing the configuration in '.$path.'eope.conf');
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

