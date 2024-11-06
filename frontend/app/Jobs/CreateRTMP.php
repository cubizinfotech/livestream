<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\RtmpLogs;

class CreateRTMP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    public $timeout = 3600; // Allows the job to run for up to 1 hour (3600 seconds).
    public $tries = 3; // Number of retry attempts

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        date_default_timezone_set(env('TIMEZONE', 'Asia/Kolkata'));
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        \Log::info('Processing task with data: ', [json_encode($data)]);

        $url = env('BACKEND_SERVER_URL').'api_generate.php';
        $this->createRtmp($url, $data);
    }

    protected function createRtmp($url, $data)
    {
        $res = callPostAPI($url, $data);
        if ($res['status'] == false) {
            $this->logs('cURL-createRtmp', $data, $res);
            throw new Exception("Error Processing Request: " . json_encode($res));
        } else {
            return true;
        }
    }

    protected function logs($type, $req, $res) 
    {
        $insertRtmpLogData = [
            'type' => $type,
            'payload' => json_encode($req),
            'response' => json_encode($res),
        ];
        RtmpLogs::create($insertRtmpLogData);
        return true;
    }
}
