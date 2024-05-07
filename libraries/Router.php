<?php

class Router
{
    private $routes = [
        "^([a-zA-Z0-9-_]+)\/?$",
        "^([a-zA-Z0-9-_]+)\/([a-zA-Z0-9-_]+)\/?$",
        "^([a-zA-Z0-9-_]+)\/([a-zA-Z0-9-_]+)\/([a-zA-Z0-9-_]+)\/?$",
        "^([a-zA-Z0-9-_]+)\/([a-zA-Z0-9-_]+)\/([a-zA-Z0-9-_]+)\/([a-zA-Z0-9-_]+)\/?$"
    ];
    private $parameters = [];

    public function __construct($url)
    {
        foreach ($this->routes as $route) {
            if (!preg_match("/" . $route . "/", $url, $matches)) {
                continue;
            }
            $this->parameters = array_slice($matches, 1);
        }
    }

    public function getParameter($index)
    {
        if(isset($this->parameters[($index - 1)])) {
            return $this->parameters[($index - 1)];
        } else {
            return "";
        }
    }

    public function createBreadcrumbs($separator = ' &raquo; ', $home = 'Home')
    {

        // This will build our "base URL" ... Also accounts for HTTPS :)
        $base = ($_SERVER['REQUEST_SCHEME'] ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';


        // Initialize a temporary array with our breadcrumbs. (starting with our home page, which I'm assuming will be the base URL)
        $breadcrumbs = array("<a href=\"$base\">$home</a>");

        // Initialize crumbs to track path for proper link
        $crumbs = '';

        // Find out the index for the last value in our path array
        $last = @end(array_keys($this->parameters));

        // Build the rest of the breadcrumbs
        foreach ($this->parameters as $x => $crumb) {
            // Our "title" is the text that will be displayed (strip out .php and turn '_' into a space)
            $title = ucwords(str_replace(array('.php', '_', '%20'), array('', ' ', ' '), $crumb));

            // If we are not on the last index, then display an <a> tag
            if ($x != $last) {
                $breadcrumbs[] = "<a href=\"$base$crumbs$crumb\">$title</a>";
                $crumbs .= $crumb . '/';
            }
            // Otherwise, just display the title (minus)
            else {
                $breadcrumbs[] = "<div class='active section'>" . $title . "</div>";
            }

        }

        // Build our temporary array (pieces of bread) into one big string :)
        return "<div class='ui breadcrumb'>" . implode($separator, $breadcrumbs) . "</div>";

    }
}
