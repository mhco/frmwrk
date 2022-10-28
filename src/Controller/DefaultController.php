<?php

namespace frmwrk\Controller;

class DefaultController
{
    public static function error(): void
    {
        header("HTTP/1.0 404 Not Found");

        echo "<h1>Error</h1>";
        echo "<p>This is the Error-page. Will be shown when a page (= controller / method) does not exist.</p>";
        echo "<p><a href='" . URL . "'>Go home</a></p>";
    }
}
