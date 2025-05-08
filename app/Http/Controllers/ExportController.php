<?php

namespace App\Http\Controllers;

use App\Jobs\ExportChunkJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ExportController extends Controller
{
    //
    public function paginateAndDispatch()
    {
        $total = DB::table('t_aset_pm_non_tik')->count();
        $chunkSize = 500;
        $pages = ceil($total / $chunkSize);

        for ($i = 0; $i < $pages; $i++) {
            $chunk = [
                'page' => $i + 1,
                'offset' => $i * $chunkSize,
                'limit' => $chunkSize,
            ];

            $jobName = "export-chunk-page-" . ($i + 1);

            ExportChunkJob::dispatch($jobName, $chunk);
        }

        Redis::set("export-chunk-page-all", $pages);
        Redis::set("export-chunk-page-total", 0);
        return response()->json(['dispatched' => $pages]);
    }
    function countJobStatuses(string $prefix = 'export-chunk-page-'): array
    {
        // dd(Redis::get('job_status:export-chunk-page-5501'));

        $keys = Redis::keys("job_status:{$prefix}*");

        $counts = [
            'queued' => 0,
            'running' => 0,
            'done' => 0,
            'failed' => 0,
            'total' => 0,
        ];

        foreach ($keys as $key) {
            $key = str_replace("laravel_database_", "", $key);
            $status = Redis::get($key);

            if (isset($counts[$status])) {
                $counts[$status]++;
            }
            $counts['total']++;
        }

        return $counts;
    }

    function getpercentage()
    {
        $all = Redis::get('export-chunk-page-all');
        $total = Redis::get('export-chunk-page-total');
        $persen = $total / $all * 100;
        return response()->json(compact('all', 'total', 'persen'));
    }
}
