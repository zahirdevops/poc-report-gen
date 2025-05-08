<?php

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\KafkaPlayController;
use App\Http\Controllers\RedisPlayGroundController;
use App\Http\Controllers\TreeBarangController;

Route::get('/', function () {
    return phpinfo();
});

Route::get('/dispatch-jobs', [ExportController::class, 'paginateAndDispatch']);
Route::get('/job-status/{name}', function ($name) {
    return ['status' => Redis::get("job_status:$name")];
});

Route::get('/getallstatus', [ExportController::class, 'countJobStatuses']);
Route::get('/tree', [TreeBarangController::class, 'tree']);
Route::get('/getpercentage', [ExportController::class, 'getpercentage']);
