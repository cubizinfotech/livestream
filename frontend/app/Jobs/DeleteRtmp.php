<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Rtmp;
use App\Models\RtmpLive;
use App\Models\RtmpRecording;
use App\Models\RtmpLogs;
use Illuminate\Support\Facades\Log;

class DeleteRtmp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    public $timeout = 3600;
    public $tries = 3;

    public function __construct($data)
    {
        date_default_timezone_set(env('TIMEZONE', 'Asia/Kolkata'));
        $this->data = $data;
    }

    public function handle()
    {
        $data = $this->data;
        $id = $data['id'];
        Log::info('Processing task with data: ', [json_encode($data)]);
        $url = env('RTMP_DELETE_URL');
        $res = $this->deleteRtmp($url, $data);
        $res = json_decode($res, true);

        if ($res['status'] == false) {
            $this->logs('cURL-deleteRtmp', $data, $res);
            throw new \Exception("Error Processing Request: " . json_encode($res));
        }
        
        Rtmp::where('id', $id)->delete();
        RtmpLive::where('rtmp_id', $id)->delete();
        RtmpRecording::where('rtmp_id', $id)->delete();
        
        return true;
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

    public function deleteRtmp($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
