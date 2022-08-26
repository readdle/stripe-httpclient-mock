<?php

require dirname(__DIR__) . '/vendor/autoload.php';

error_reporting(E_ALL);

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle): bool
    {
        return (string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
