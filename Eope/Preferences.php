<?php

/**
 * Preferences.php
 * 
 * EOPE - Enygmata Own PHP Editor
 * 
 * @author     Higor "enygmata" Eurípedes
 * @copyright  Higor "enygmata" Eurípedes (c) 2008
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL 
 */

require_once(APP_DIR . 'PreferencesSignals.php');

class Preferences extends EtkWindow
{
	public function __construct ()
    {
        parent::__construct(APP_DIR . 'Glade/preferences.glade','window');
        $this->connect_glade_to(new PreferencesSignals($this));
        $this->show_all();
	}
}
