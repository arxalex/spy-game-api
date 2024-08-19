<?php

use framework\routing\Router;

$uri_string = parse_url($_SERVER['REQUEST_URI'])['path'];
$uri = explode("/", $uri_string);

require_once("SplClassLoader.php");

$namespaces = [
    'framework',
    'framework\database',
    'framework\endpoints',
    'framework\models',
    'framework\renders',
    'framework\routing',
    'framework\repositories',
    'framework\utils'
];

$externalNamespaces = json_decode(file_get_contents("system/namespaces.json"));

$namespaces = array_merge($namespaces, $externalNamespaces);

foreach($namespaces as $value){
    $loader = new SplClassLoader($value);
    $loader->register();
}

error_reporting(E_ERROR | E_PARSE);

Router::resolveRoute($uri_string);




