<?php

if (!function_exists('is_image')) {
    function is_image($mimeType)
    {
        return starts_with($mimeType, 'image/');
    }
}

if (!function_exists('get_status_code')) {
    function get_status_code($url)
    {
        try {
            return (new \GuzzleHttp\Client())->head($url)->getStatusCode();
        } catch (Exception $e) {
            return 0;
        }
    }
}
