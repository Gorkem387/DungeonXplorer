<?php

class Router
{
    private $routes = [];
    private $prefix;

    public function __construct($prefix = '')
    {
        $this->prefix = trim($prefix, '/');
    }

    public function addRoute($uri, $controllerMethod)
    {
        $this->routes[trim($uri, '/')] = $controllerMethod;
    }

    public function route($url)
    {
        echo "URL reçue par le Router : '$url'<br>";
    if ($this->prefix && strpos($url, $this->prefix) === 0) {
        $url = substr($url, strlen($this->prefix) + 1);
    }

    $url = trim($url, '/');
    
    echo "Routes enregistrées : <pre>" . print_r($this->routes, true) . "</pre>";
    echo "Nombre de routes : " . count($this->routes) . "<br>";

    foreach ($this->routes as $route => $controllerMethod) {
            
            echo "<br>--- Test de la route '$route' ---<br>";
            
            $routeParts = explode('/', $route);
            $urlParts = explode('/', $url);
            
            echo "Route parts : " . print_r($routeParts, true) . "<br>";
            echo "URL parts : " . print_r($urlParts, true) . "<br>";
            echo "Nombre de parts route : " . count($routeParts) . "<br>";
            echo "Nombre de parts URL : " . count($urlParts) . "<br>";
    
            if (count($routeParts) === count($urlParts)) {
                
                $params = [];
                $isMatch = true;
    
                foreach ($routeParts as $index => $part) {
                    echo "Compare '$part' avec '{$urlParts[$index]}'<br>";
                    
                    if (preg_match('/^{\w+}$/', $part)) {
                        $params[] = $urlParts[$index];
                    } elseif ($part !== $urlParts[$index]) {
                        $isMatch = false;
                        echo "PAS DE MATCH !<br>";
                        break;
                    }
                }
    
                if ($isMatch) {
                    echo "MATCH TROUVÉ ! Appel de $controllerMethod<br>";
                    list($controllerName, $methodName) = explode('@', $controllerMethod);
                    $controller = new $controllerName();
                    call_user_func_array([$controller, $methodName], $params);
                    return;
                }
            }
        }
    
        require_once 'views/404.php';
    }
}