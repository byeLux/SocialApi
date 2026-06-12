<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/config');
$dotenv->load();
