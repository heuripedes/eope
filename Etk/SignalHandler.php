<?php

abstract class EtkSignalHandler
{
    public function __construct (EtkApplication $application, EtkWindow $window)
    {
        $this->application = $application;
        $this->window = $window;
    }
}
