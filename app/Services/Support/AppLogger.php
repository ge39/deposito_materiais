<?php
namespace App\Services\Support;

use Illuminate\Support\Facades\Log;

class AppLogger
{
    public static function info(string $msg, array $context = [])
    {
        Log::info($msg, $context);
    }

    public static function error(string $msg, array $context = [])
    {
        Log::error($msg, $context);
    }
}