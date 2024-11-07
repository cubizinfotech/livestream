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
        $res = $this->callPostAPI($url, $data);
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

    public function callPostAPI($url, $data)
    {
        // Initialize cURL session
        $ch = curl_init($url);

        // Configure cURL options for a POST request
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Send data as URL-encoded form
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response instead of outputting it
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (use with caution)

        // Execute the request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            $res = [
                'status' => false,
                'message' => "cURL Error: " . curl_error($ch),
            ];
        } else {
            $res = [
                'status' => true,
                'message' => $response,
            ];
        }

        // Close the cURL session
        curl_close($ch);

        return $res;
    }
}
