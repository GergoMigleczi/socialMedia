<?php
namespace Core;

class Router
{
    /**
     * Array of registered routes
     * @var array
     */
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];
    
    /**
     * Register a GET route
     *
     * @param string $route The URL path
     * @param string $handler Controller@method notation
     * @return void
     */
    public function get($route, $handler)
    {
        $this->addRoute('GET', $route, $handler);
    }
    
    /**
     * Register a POST route
     *
     * @param string $route The URL path
     * @param string $handler Controller@method notation
     * @return void
     */
    public function post($route, $handler)
    {
        $this->addRoute('POST', $route, $handler);
    }
    
    /**
     * Register a PUT route
     *
     * @param string $route The URL path
     * @param string $handler Controller@method notation
     * @return void
     */
    public function put($route, $handler)
    {
        $this->addRoute('PUT', $route, $handler);
    }
    
    /**
     * Register a DELETE route
     *
     * @param string $route The URL path
     * @param string $handler Controller@method notation
     * @return void
     */
    public function delete($route, $handler)
    {
        $this->addRoute('DELETE', $route, $handler);
    }
    
    /**
     * Add a route to the routing table
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $route The URL path
     * @param string $handler Controller@method notation
     * @return void
     */
    protected function addRoute($method, $route, $handler)
    {
        // Convert route to regex pattern for matching
        $pattern = $this->routeToRegex($route);
        
        $this->routes[$method][$pattern] = [
            'route' => $route,
            'handler' => $handler,
            'params' => []
        ];
    }
    
    /**
     * Convert a route to a regex pattern for matching
     * Support for parameters like {id} in routes
     *
     * @param string $route The URL path
     * @return string The regex pattern
     */
    protected function routeToRegex($route)
    {
        // Replace route parameters like {id} with regex pattern
        $route = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
        
        // Escape forward slashes and add start/end anchors
        return "#^" . str_replace('/', '\/', $route) . "$#";
    }
    
    /**
     * Match the current request URL to a registered route
     *
     * @param string $url The URL to match
     * @param string $method The HTTP method
     * @return array|boolean The matched route or false if no match
     */
    protected function matchRoute($url, $method)
    {
        if (!isset($this->routes[$method])) {
            return false;
        }
        
        foreach ($this->routes[$method] as $pattern => $route) {
            if (preg_match($pattern, $url, $matches)) {
                // Extract named parameters
                foreach ($matches as $key => $match) {
                    // Skip numeric keys, only use named parameters
                    if (is_string($key)) {
                        $route['params'][$key] = $match;
                    }
                }
                return $route;
            }
        }
        
        return false;
    }
    
    /**
     * Dispatch the route for the current request
     *
     * @return void
     */
    public function dispatch()
    {
        // Get current URL and HTTP method
        $url = $this->getUrl();
        $method = $this->getRequestMethod();
        
        // Try to match a route
        $route = $this->matchRoute($url, $method);
        
        if ($route) {
            // Parse controller and method from handler string
            list($controller, $action) = explode('@', $route['handler']);
            
            // If controller doesn't have namespace, assume it's in Controllers namespace
            if (strpos($controller, '\\') === false) {
                $controller = "Controllers\\$controller";
            }
            
            // Check if controller class exists
            if (class_exists($controller)) {
                $controllerInstance = new $controller();
                
                // Check if the action method exists
                if (method_exists($controllerInstance, $action)) {
                    // Call the controller action with the route parameters
                    call_user_func_array([$controllerInstance, $action], $route['params']);
                    return;
                }
            }
            
            $this->handleError(404, "Controller action not found: $controller@$action");
        } else {
            $this->handleError(404, "No route found for $method $url");
        }
    }
    
    /**
     * Get the current URL from the request
     *
     * @return string The cleaned URL
     */
    protected function getUrl()
    {
        // Get the URL from the request
        $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        
        // Remove query string if present
        $position = strpos($url, '?');
        if ($position !== false) {
            $url = substr($url, 0, $position);
        }
        
        // Get the base path (e.g. if the app is in a subdirectory)
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        
        // Remove the base path from the URL
        if ($basePath !== '/' && strpos($url, $basePath) === 0) {
            $url = substr($url, strlen($basePath));
        }
        
        // Ensure URL starts with /
        if (empty($url) || $url === '/index.php') {
            $url = '/';
        }
        
        return $url;
    }
    
    /**
     * Get the HTTP request method
     *
     * @return string The request method (GET, POST, etc.)
     */
    protected function getRequestMethod()
    {
        // Get the HTTP method or default to GET
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        
        // Support for method override via _method POST parameter or X-HTTP-Method-Override header
        if ($method === 'POST') {
            if (isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
            } elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            }
        }
        
        return $method;
    }
    
    /**
     * Handle routing errors
     *
     * @param int $code HTTP status code
     * @param string $message Error message
     * @return void
     */
    protected function handleError($code, $message)
    {
        http_response_code($code);
        
        // If we're in development mode, show detailed error
        if (defined('APP_ENV') && APP_ENV === 'development') {
            echo "<h1>Error $code</h1>";
            echo "<p>$message</p>";
        } else {
            // In production, show a generic error page
            if ($code === 404) {
                if (file_exists(VIEWS_PATH . '/errors/404.php')) {
                    require_once VIEWS_PATH . '/errors/404.php';
                } else {
                    echo "<h1>Page Not Found</h1>";
                }
            } else {
                if (file_exists(VIEWS_PATH . '/errors/500.php')) {
                    require_once VIEWS_PATH . '/errors/500.php';
                } else {
                    echo "<h1>Server Error</h1>";
                }
            }
        }
        
        exit;
    }
}