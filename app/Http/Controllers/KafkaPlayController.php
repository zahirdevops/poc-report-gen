<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;


class KafkaPlayController extends Controller
{
    //

    function index()
    {
        $message = new Message(
            body: ['nama' => "Firmansyah"],
        );

        try {
            Kafka::publish('broker')
                ->onTopic('firmannopikkk')
                ->withDebugEnabled()
                ->withMessage($message)
                ->send();

            echo "Published page \n";
        } catch (\Exception $e) {
            echo "Failed to publish page : " . $e->getMessage() . "\n";
        }
    }

    public function paginateAndSendToKafka()
    {
        $total = DB::table('t_aset_pm_non_tik')->count();
        $chunkSize = 200;
        $pages = ceil($total / $chunkSize);




        for ($i = 0; $i < $pages; $i++) {
            $chunk = [
                'page' => $i + 1,
                'offset' => $i * $chunkSize,
                'limit' => $chunkSize,
            ];

            $message = new Message(
                body: $chunk,
            );

            try {
                Kafka::publish('testbroker')
                    ->onTopic('firmannopikkk')
                    ->withMessage($message)
                    ->send();

                echo "Published page {$i}\n";
            } catch (\Exception $e) {
                echo "Failed to publish page {$i}: " . $e->getMessage() . "\n";
            }
        }
    }
}
