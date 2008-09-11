<?php

class SciHighlightHTML extends SciHighlight
{
    public function setup_highlight ()
    {
        $this->sci->set_lexer(GtkScintilla::SCINTILLA_LEX_HTML);
        $this->sci->set_style_bits(7);
        $this->sci->set_property('fold.html', '1');
        $this->sci->set_property('fold.html.preprocessor', '1');
        $this->sci->style_set_fore(SCE_H_TAG,        sci_color('0000FF'));
        $this->sci->style_set_fore(SCE_H_TAGEND,     sci_color('0000FF'));
        $this->sci->style_set_fore(SCE_H_TAGUNKNOWN, sci_color('FF0000'));
    }
    
    public function setup_folding ()
    {
    }
}
