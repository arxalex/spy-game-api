<?php

namespace framework\routing;

use framework\utils\ConstantsHelper;

class Router
{
    public static function resolveRoute(string $uri_string)
    {
        $routeRegistrer = new RouteRegister(ConstantsHelper::SYSTEM_PATH . "/routes.json");
        $class = $routeRegistrer->getClassByRoute($uri_string);

        if ($class == null) {
            http_response_code(404);
            exit;
        } else {
            $params = json_decode(file_get_contents("php://input"), true);

            $className = $class['className'];
            $classObject = new $className();

            $classObject->render($params);
        }
    }
}
