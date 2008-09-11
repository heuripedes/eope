<?php

require_once APP_DIR . 'Classes/Scintilla/SciHighlightHTML.php';

class SciHighlightPHP extends SciHighlightHTML
{
    public function setup_highlight ()
    {
        $this->sci->set_property('phpscript.mode', '1');
        parent::setup_highlight();
        $this->sci->style_set_fore(SCE_HPHP_DEFAULT,          sci_color('000000'));
        $this->sci->style_set_fore(SCE_HPHP_VARIABLE,         sci_color('0000BB'));
        $this->sci->style_set_fore(SCE_HPHP_WORD,             sci_color('007700'));
        $this->sci->style_set_fore(SCE_HPHP_NUMBER,           sci_color('007700'));
        $this->sci->style_set_fore(SCE_HPHP_OPERATOR,         sci_color('0000BB'));
        $this->sci->style_set_fore(SCE_HPHP_HSTRING,          sci_color('DD0000'));
        $this->sci->style_set_fore(SCE_HPHP_SIMPLESTRING,     sci_color('DD0000'));
        $this->sci->style_set_fore(SCE_HPHP_COMMENT,          sci_color('FFA500'));
        $this->sci->style_set_fore(SCE_HPHP_COMMENTLINE,      sci_color('FF9900'));
        $this->sci->style_set_fore(SCE_HPHP_COMPLEX_VARIABLE, sci_color('0000BB'));
        $this->sci->style_set_fore(SCE_HPHP_HSTRING_VARIABLE, sci_color('0000BB'));
		
		$this->sci->set_keywords(0, 'php');
        $this->sci->set_keywords(4, 'abstract and array as bool boolean break case catch cfunction __class__ class clone const continue declare default die __dir__ directory do double echo else elseif empty enddeclare endfor endforeach endif endswitch endwhile eval exception exit extends false __file__ final float for foreach __function__ function goto global if implements include include_once int integer interface isset __line__ list __method__ namespace __namespace__ new null object old_function or parent php_user_filter print private protected public real require require_once resource return __sleep static stdclass string switch this throw true try unset use var __wakeup while xor');
    }
    
    public function setup_folding ()
    {
    }
}
