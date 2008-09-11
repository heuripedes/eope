<?php

/**
 * SciBase.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

require_once APP_DIR . 'Classes/Scintilla/SciConstants.php';

require_once APP_DIR . 'Classes/Scintilla/SciHighlightHTML.php';
require_once APP_DIR . 'Classes/Scintilla/SciHighlightPHP.php';

define('SC_XPM_MARK_ARROWUP',   file_get_contents(APP_DIR.'Classes/Scintilla/arrowup.xpm'));
define('SC_XPM_MARK_ARROWDOWN', file_get_contents(APP_DIR.'Classes/Scintilla/arrowdown.xpm'));
define('SC_XPM_MARK_ARROW', file_get_contents(APP_DIR.'Classes/Scintilla/arrowright.xpm'));

// TODO: add more highlighters
abstract class SciHighlight
{
    protected $sci;
    
    public function __construct ($sci)
    {
        $this->sci = $sci;
        
        
        $sci->indic_set_style(2, INDIC_SQUIGGLE);

        $sci->indic_set_fore(0, sci_color('000000'));
        $sci->indic_set_fore(2, sci_color('000000'));
    }
}

class SciHighlightFactory 
{
    private static $langs = array('PHP', 'HTML');
    public static function get_highlighter ($lang, $sci)
    {
        if (in_array($lang, self::$langs))
        {
            $name = 'SciHighlight' . $lang;
            return new $name($sci);
        }
        return null;
    }
}

function invert($icolour)
{
	$r = 0xffffff - $icolour;
	$g = 0xffffff - ($icolour >> 8);
	$b = 0xffffff - ($icolour >> 16);
	return ($r | ($g << 8) | ($b << 16));
}

function sci_color ($hcolor)
{
    //$hcolor = strrev($hcolor);
    /*$r = $hcolor[0].$hcolor[1];
    $g = $hcolor[2].$hcolor[3];
    $b = $hcolor[4].$hcolor[5];
    */
    //return (int)($b.$g.$r);
    //$hcolor = hexdec($hcolor);
    return  hexdec(strrev($hcolor));
    $r = 0xffffff - $hcolor;
    $g = 0xffffff - ($hcolor >> 8);
    $b = 0xffffff - ($hcolor >> 16);
    
    return ($r | ($g << 8) | ($b << 16));
    //return (hexdec($r) | hexdec($g) << 8 | hexdec($r) << 16);
    //return  hexdec(strrev($hcolor));
}



