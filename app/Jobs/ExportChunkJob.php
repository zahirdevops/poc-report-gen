<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExportChunkJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public string $jobName;
    public array $chunk;
    public string $table = "t_aset_pm_non_tik";
    public function __construct(string $jobName, array $chunk)
    {
        $this->jobName = $jobName;
        $this->chunk = $chunk;

        Redis::set("job_status:{$this->jobName}", 'queued');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Redis::set("job_status:{$this->jobName}", 'running');
        // sleep(5);
        $limit = $this->chunk['limit'];
        $offset = $this->chunk['offset'];

        // ✅ Query database
        $rows = DB::table($this->table)
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->toArray();

        // ✅ Convert to JSON
        $json = json_encode($rows, JSON_PRETTY_PRINT);
        // dd($json);

        // ✅ File name
        $filename = "{$this->jobName}-{$limit}-{$offset}-{$this->table}.json";
        $path = "exports/{$filename}";

        // ✅ Store in storage/app/exports/
        Storage::disk('local')->put($path, $json);
        Redis::set("job_status:{$this->jobName}", 'done');
        Redis::incr("export-chunk-page-total");
    }
    public function failed(\Throwable $e): void
    {
        Redis::set("job_status:{$this->jobName}", 'failed');
    }
}
