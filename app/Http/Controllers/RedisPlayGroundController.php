<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class RedisPlayGroundController extends Controller
{
    //
    function setter()
    {
        $data = [
            'id' => 'laravel123',
            'percentage' => 90.9,
            'completed' => false,
        ];

        // Store to Redis
        Redis::set('task:laravel123', json_encode($data));
    }

    function readTaskJson()
    {
        $raw = Redis::get("task:laravel123");

        return $raw ? json_decode($raw, true) : null;
    }
}
