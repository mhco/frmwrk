<?php

//print input with pre tags surrounding it, making it legible in the browser
//pa stands for "print all"
function pa ($array)
{
    echo "<pre>";
        print_r($array);
    echo "</pre>";
}

function get_ip ()
{
    if (getenv('HTTP_CLIENT_IP') and strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
    {
        $ip_address = getenv('HTTP_CLIENT_IP');
    }
    elseif (getenv('HTTP_X_FORWARDED_FOR') and strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown'))
    {
        $ip_address = getenv('HTTP_X_FORWARDED_FOR');
    }
    elseif (getenv('REMOTE_ADDR') and strcasecmp(getenv('REMOTE_ADDR'), 'unknown'))
    {
        $ip_address = getenv('REMOTE_ADDR');
    }
    elseif (isset($_SERVER['REMOTE_ADDR']) and $_SERVER['REMOTE_ADDR'] and strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown'))
    {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    else
    {
        $ip_address = 'Unknown';
    }

    return $ip_address;
}

function redirect ($url = null)
{
    $url = URL . str_replace(URL, '', $url);
    header('Location: ' . $url);
    exit;
}

//localization: translate function
function __()
{
    global $localization;
    $args = func_get_args();
    return $localization->getTranslation(func_get_args());
}
