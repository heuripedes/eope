<?php

function is_loadable ($file)
{
    return (file_exists($file) && is_readable($file));
}

