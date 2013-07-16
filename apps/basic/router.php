<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = $_SERVER['DOCUMENT_ROOT'];

if (file_exists($root . '/' . $uri)) {
    return false;
} else {
    include_once $root . '/index.php';
}
