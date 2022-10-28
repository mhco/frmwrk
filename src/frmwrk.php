<?php

namespace frmwrk;

//use Hewitt\Core\Route as Route;
//use Hewitt\Core\Localization;

class frmwrk
{
    //load config
    public static function init ()
    {
        echo "test";
        exit;
        /*
        |--------------------------------------------------------------------------
        | Error reporting
        | Useful to show every little problem during development, but only show hard errors in production
        |--------------------------------------------------------------------------
        */
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        /*
        |--------------------------------------------------------------------------
        | Set Constants
        | Some things are vital to the operation of this framework, so we need to
        | define constants that won't change throughout the app's runtime.
        |--------------------------------------------------------------------------
        */
        self::setConstants();

        //$this->defineApplicationSettings();

        /*
        |--------------------------------------------------------------------------
        | Load Functions
        | We want to be able to define some default functions that most projects
        | will benefit from.
        |--------------------------------------------------------------------------
        */
        self::loadFunctions();

        /*
        |--------------------------------------------------------------------------
        | Date Formats
        |--------------------------------------------------------------------------
        |
        | We need to have a standard for date formatting, as we're an international
        | company. We should always abide by the ISO-8601 standard. This makes it
        | a lot easier to utilize that standard without having to write it out.
        |
        */
        define('TIME_FORMAT', 'Y-m-d H:i:s T');
        define('TIME_FORMAT_DATE', 'Y-m-d');

        /*
        |--------------------------------------------------------------------------
        | Timezone
        |--------------------------------------------------------------------------
        |
        | I'm on the East Coast, so times for me should all be in EST/EDT, but some
        | peope live in other places. We'll set the timezone for them when we
        | initialize their user object.
        |
        */
        date_default_timezone_set('America/New_York');

        /*
        |--------------------------------------------------------------------------
        | Traffic Routing
        |--------------------------------------------------------------------------
        |
        | Specify what users should see based on URLs requested. See documentation
        | at "docs/Routing.md" for more information.
        |
        */
        require_once 'Core/Router.php';

        //routes
        $routers = array_diff(scandir(APP . '/Routes'), ['.','..']);
        foreach ($routers as $router) require_once APP . '/Routes/' . $router;

        //process the routes when the user loads the page
        //Route::init();
    }

    public static function setConstants (): void
    {
        if (!defined('ROOT'))
        {
            $root = str_replace('\\', '/', dirname(__DIR__));
            $root = substr($root, 0, strrpos($root, '/vendor/'));
            define('ROOT', $root);
        }

        define('APP', ROOT . '/app');
        define('PUBLIC_FOLDER', 'public');
        define('PUBLIC_PATH', ROOT . '/' . PUBLIC_FOLDER);
        define('BASEPATH', str_replace($_SERVER['DOCUMENT_ROOT'], '', PUBLIC_PATH) . '/');
        if (
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) and $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            or (!empty($_SERVER['REQUEST_SCHEME']) and $_SERVER['REQUEST_SCHEME'] == 'https')
            or (!empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on')
            or (!empty($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] == '443')
        ) define('URL_PROTOCOL', 'https://');
        else define('URL_PROTOCOL', 'http://');
        define('URL_SUB_FOLDER', str_replace(['\\', PUBLIC_FOLDER], ['/', ''], dirname($_SERVER['SCRIPT_NAME'])));
        define('URL_DOMAIN', $_SERVER['HTTP_HOST']);
        define('URL', rtrim(URL_PROTOCOL . URL_DOMAIN . BASEPATH, '/'));
    }

    /*
    |--------------------------------------------------------------------------
    | Define application settings
    | Some settings need to change, so we don't store these as constants, but
    | we will attach them to the frmwrk object because they relate to the app.
    |--------------------------------------------------------------------------
    |
    */
    public function defineApplicationSettings (): void
    {
        $this->maintenance_mode = false;
        $this->default_language = 'en-us';
    }

    //load functions
    public static function loadFunctions (): void
    {
        require_once 'Functions/Default.php';
    }

    //load database class

    //error controllers

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    |
    | People speak different languages. This allows users to change the site's
    | language for better understanding and customization.
    |
    */
    public static function localization (): void
    {
        $localization = new Localization();
    }
}

//defineApplicationSettings
//maintenance mode
