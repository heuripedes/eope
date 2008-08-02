<?php

function get_mime_by_ext ($ext, $mimeno = 0)
{
	$mimes = array(
		'css' => array('text/css'),
		'html' => array('text/html'),
		'htm' => array('text/html'),
		//'nemerle' => array('text/x-nemerle'),
		'cs' => array('text/x-csharp'),
		'makefile' => array('text/x-makefile'),
		'po' => array('text/x-gettext-translation', 'text/x-gettext-translation-template'),
		'java' => array('text/x-java'),
		//'verilog' => array('text/x-verilog-src'),
		'd' => array('text/x-dsrc'),
		'c' => array('text/x-csrc', 'text/x-chdr'),
		'h' => array('text/x-csrc', 'text/x-chdr'),
		'pas' => array('text/x-pascal'),
		'docbook' => array('application/docbook+xml'),
		//'objective caml' => array('text/x-ocaml'),
		//'texinfo' => array('text/x-texinfo'),
		'sql' => array('text/x-sql'),
		'.desktop' => array('application/x-desktop', 'application/x-gnome-app-info'),
		'pl' => array('application/x-perl'),
		'ada' => array('text/x-adasrc'),
		//'octave' => array('text/x-octave', 'text/x-matlab'),
		//'haskell' => array('text/x-haskell'),
		'lua' => array('text/x-lua'),
		'tcl' => array('text/x-tcl'),
		'js' => array('application/x-javascript'),
		'rb' => array('application/x-ruby'),
		'tex' => array('text/x-tex'),
		//'msil' => array('text/x-msil'),
		'scheme' => array('text/x-scheme'),
		'dif' => array('text/x-patch'),
		'diff' => array('text/x-patch'),
		'patch' => array('text/x-patch'),
		'.ini' => array('text/x-ini-file', 'application/x-ini-file'),
		'.conf' => array('text/x-ini-file', 'application/x-ini-file'),
		'gtkrc' => array('text/x-gtkrc'),
		'php' => array('application/x-php'),
		'php4' => array('application/x-php'),
		'phpw' => array('application/x-php'),
		'py' => array('text/x-python'),
		//'vb.net' => array('text/x-vbnet', 'text/x-vb'),
		'r' => array('text/x-r'),
		//'boo' => array('text/x-boo'),
		'xml' => array('application/xml', 'text/xml'),
		'xsl' => array('application/xml', 'text/xml'),
		'sh' => array('application/x-shellscript'),
		'changelog' => array('text/x-changelog'),
		//'idl' => array('text/x-idl'),
		'cxx' => array('text/x-c++src', 'text/x-c++hdr'),
		'hxx' => array('text/x-c++src', 'text/x-c++hdr'),
		'vhdl' => array('text/x-vhdl'),
		//'especificação de rpm' => array('text/x-rpm-spec'),
		'old' => array('text/x-fortran'),
		'f77' => array('text/x-fortran'),
		'f99' => array('text/x-fortran')
	);
	
	$ext = strtolower($ext);
	
	if (!isset($mimes[$ext]))
	{
		return 'text/plain';
	}
	
	if (isset($mimes[$ext][$mimeno]))
	{
	 	return $mimes[$ext][$mimeno];
	}
	else
	{
		return $mimes[$ext][0];
	}
}
