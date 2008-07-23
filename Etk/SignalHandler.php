<?php

abstract class EtkSignalHandler extends EtkObject
{
    public function __construct (EtkApplication $application, EtkWindow $window)
    {
        $this->application = $application;
        $this->window = $window;
    }
}
