<?php

if (!function_exists('is_image')) {
    function is_image($mimeType)
    {
        return starts_with($mimeType, 'image/');
    }
}

if (!function_exists('get_status_code')) {
    function get_status_code($url, $timeout = 10)
    {
        try {
            return (new \GuzzleHttp\Client())->head($url, [
                'timeout' => $timeout,
                'connect_timeout' => $timeout,
            ])->getStatusCode();
        } catch (Exception $e) {
            return 0;
        }
    }
}
