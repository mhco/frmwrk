<?php

namespace frmwrk\Core;

class Route
{
    /*
    |--------------------------------------------------------------------------
    | Settings and variables
    |--------------------------------------------------------------------------
    |
    | Change these values with the helper methods below, so they aren't
    | hardcoded in this file. Having setting updates separate enables easy and
    | quick updating of this file. Abstraction is good.
    |
    */
    protected static $basepath = '';
    protected static $case_matters = false;
    protected static $trailing_slash_matters = false;
    protected static $multimatch = false;

    // Error pages
    protected static $pathNotFound = 'frmwrk\Controller\DefaultController::error'; //couldn't find the right path, class, or class method
    protected static $methodNotAllowed = 'frmwrk\Controller\DefaultController::error'; //couldn't find the right route based on the request method (get/post)

    // Array containing all defined routes
    protected static $routes = [];

    /*
    |--------------------------------------------------------------------------
    | Functions used to add a new route
    |--------------------------------------------------------------------------
    |
    | @param string $expression    Route string or expression
    | @param callable $function    Function to call if route with allowed method is found
    | @param string|array $method  Either a string of allowed method or an array with string values
    |
    */
    public static function add($expression, $function, $method = 'get'): void
    {
        $args = array_slice(func_get_args(), 3);

        array_push(self::$routes, [
            'expression' => $expression,
            'function' => $function,
            'method' => $method,
            'arguments' => (count($args) ? $args[0] : [])
        ]);
    }
    public static function get($expression, $function): void
    {
        $args = array_slice(func_get_args(), 2);

        array_push(self::$routes, [
            'expression' => $expression,
            'function' => $function,
            'method' => 'get',
            'arguments' => (count($args) ? $args[0] : [])
        ]);
    }
    public static function post($expression, $function): void
    {
        $args = array_slice(func_get_args(), 2);

        array_push(self::$routes, [
            'expression' => $expression,
            'function' => $function,
            'method' => 'post',
            'arguments' => (count($args) ? $args[0] : [])
        ]);
    }

    // Return a list of all routes that have been added
    public static function getAll(): array { return self::$routes; }

    /*
    |--------------------------------------------------------------------------
    | Helper Functions
    |--------------------------------------------------------------------------
    |
    | Set class settings so they aren't hardcoded into this file
    |
    | @param string $value      Value that the variable will be changed to
    |
    */
    public static function basepath($value): void { self::$basepath = $value; }
    public static function case_matters($value): void { self::$case_matters = $value; }
    public static function trailing_slash_matters($value): void { self::$trailing_slash_matters = $value; }
    public static function multimatch($value): void { self::$multimatch = $value; }
    public static function pathNotFound($value): void { self::$pathNotFound = $value; }
    public static function methodNotAllowed($value): void { self::$methodNotAllowed = $value; }

    /*
    |--------------------------------------------------------------------------
    | Main Methods
    |--------------------------------------------------------------------------
    |
    | There are three main functions: INIT, FILTER, and RUN. Each are required,
    | but existing functions can be overwritten, and more can be added. To do
    | this, create a new file and extend this class, so you can continue to
    | update the script while keeping your changes safe.
    |
    */

    // Gets the list of routes and passes a list of routes that match the requested route to filter()
    public static function init(): void
    {
        // The basepath never needs a trailing slash
        // Because the trailing slash will be added using the route expressions
        $basepath = rtrim(self::$basepath, '/');

        // Parse current URL
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);
        $path = '/';

        // If there is a path available
        if (isset($parsed_url['path']))
        {
            // If the trailing slash matters
            if (self::$trailing_slash_matters)
            {
                $path = $parsed_url['path'];
            }
            else
            {
                // If the path is not equal to the base path (including a trailing slash)
                if ($basepath.'/' != $parsed_url['path'])
                {
                    // Cut the trailing slash away because it does not matters
                    $path = rtrim($parsed_url['path'], '/');
                }
                else
                {
                    $path = $parsed_url['path'];
                }
            }
        }
        $path = urldecode($path);

        // Get current request method
        $method = $_SERVER['REQUEST_METHOD'];

        $matching_routes = [];

        // Loop through routes
        foreach (static::$routes as $route)
        {
            echo '[' . $basepath . ']<br>';
            pa($route);
            // Add basepath to matching string
            if ($basepath != '' and $basepath != '/')
            {
                $route['expression'] = '('.$basepath.')'.$route['expression'];
            }

            if (preg_match('#^'.$route['expression'].'$#'.(self::$case_matters ? '' : 'i').'u', $path, $matches))
            {
                // Always remove first element; this contains the whole path string, and is useless to us
                array_shift($matches);

                // Remove basepath
                if ($basepath != '' && $basepath != '/') array_shift($matches);

                $route['matches'] = $matches;

                // Build list of matching routes that are also callable
                // Is the function a class::method?
                if (is_string($route) and !empty($route))
                {
                    // Is the defined function callable?
                    if (is_callable($route))
                    {
                        $matching_routes[] = $route;
                    }
                }
                elseif (is_array($route) and isset($route['function']))
                {
                    // Is the defined function callable?
                    if (is_callable($route['function']))
                    {
                        $matching_routes[] = $route;
                    }
                }
            }
        }

        // Are there any routes that work?
        if (!count($matching_routes))
        // No
        {
            // Display 404 error page
            static::run(self::$pathNotFound);
            exit;
        }

        $routes = $matching_routes;
        $matching_routes = [];

        // Loop through list of matching routes
        foreach ($routes as $route)
        {
            // Cast allowed method to array if it's not one already, then run through all methods
            foreach ((array)$route['method'] as $request_method)
            {
                // Check method match
                if (strtolower($method) == strtolower($request_method))
                {
                    // Build list of routes with matching request types
                    $matching_routes[] = $route;
                }
            }
        }

        // Are there any routes that work?
        if (count($matching_routes))
        // Yes
        {
            static::filter($matching_routes);
        }
        // No
        else
        {
            // Display 405 error page
            static::run(self::$methodNotAllowed);
            exit;
        }
    }

    // Filter the matching results from one/many to just one
    public static function filter($matching_routes): void
    {
        if (count($matching_routes))
        {
            static::run($matching_routes[0]);
        }
        else
        {
            static::run(self::$pathNotFound);
            exit;
        }
    }

    // Calls defined class::method or embedded function to run the page
    public static function run($route): void
    {
        // Is the function a class::method?
        if (is_string($route) and !empty($route))
        {
            call_user_func_array($route, []);
        }
        elseif (is_array($route) and isset($route['function']))
        {
            if (is_string($route['function']) and strpos($route['function'], '::') !== false)
            {
                // Is the defined function callable?
                if (is_callable($route['function']))
                {
                    list($class, $method) = explode('::', $route['function'], 2);

                    $obj = new $class();
                    call_user_func_array($route['function'], $route['matches']);
                }
            }
            elseif (is_object($route['function']) and is_callable($route['function']))
            {
                call_user_func_array($route['function'], []);
            }
        }
        else
        {
            call_user_func_array(self::$pathNotFound, []);
            exit;
        }
    }
}
