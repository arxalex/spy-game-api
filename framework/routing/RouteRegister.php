<?php

namespace framework\routing;

class RouteRegister
{
    private array $routes;

    public function __construct(string $routes)
    {
        $this->routes = json_decode(file_get_contents($routes));
    }
    public function getClassByRoute(string $uri_string)
    {
        $uris = explode("/", $uri_string);

        if ($uris[count($uris) - 1] == "") {
            array_pop($uris);
        }

        $routes = $this->routes;
        $className = "";
        $params = array();
        foreach ($uris as $key => $uri) {
            $routeType = "default";
            if (is_numeric($uri)) {
                $routeType = "int";
            }
            foreach ($routes as $value) {
                if ($value->routeName == $uri && $value->routeType == $routeType) {
                    $routes = $value->contants;
                    $className = $value->className;
                    if (count($uris) - 1 == $key) {
                        return [
                            "className" => $className,
                            "params" => $params
                        ];
                    }
                    continue 2;
                }
                if (is_numeric($uri) && $value->routeType == $routeType) {
                    $routes = $value->contants;
                    $className = $value->className;
                    $params[$value->routeName] = $uri;
                    if (count($uris) - 1 == $key) {
                        return [
                            "className" => $className,
                            "params" => $params
                        ];
                    }
                    continue 2;
                }
            }
            return;
        }
        return;
    }
}
