<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rtmp;
use App\Models\RtmpLive;
use App\Models\RtmpRecording;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use App\Jobs\ProcessStream;

class ApiController extends Controller
{
    public function __constructor()
    {
        date_default_timezone_set(env('TIMEZONE', 'Asia/Kolkata'));
    }
    
    public function streamStart(Request $request) 
    {
        $this->logs($request->all(), 'streamStart');

        try {
            $getRtmp = Rtmp::with('rtmp_live')->where('stream_key', $request->name)->first();

            if(isset($getRtmp->id) && isset($getRtmp->rtmp_live->id)) {
                return response()->json(['status' => false, 'message' => "Blocked the stream!"], 404);
            }
            else if(isset($getRtmp->id)) {
                $insertRtmpLiveData = [
                    'rtmp_id' => $getRtmp->id,
                ];
                RtmpLive::create($insertRtmpLiveData);
                return response()->json(['status' => true, 'message' => 'Stream started successfully.'], 200);
            }
            else {
                return response()->json(['status' => false, 'message' => "Something went wrong!"], 404);  
            }
        } catch (Exception $ex) {
            return response()->json(['status' => false, 'message' => $ex->Message()], 404);
        }
    }

    public function streamStop(Request $request) 
    {
        $this->logs($request->all(), 'streamStop');

        try {
            $getRtmp = Rtmp::with('rtmp_live')->where('stream_key', $request->name)->first();

            if(isset($getRtmp->id) && isset($getRtmp->rtmp_live->id)) {
                RtmpLive::where('id', $getRtmp->rtmp_live->id)->delete();
                return response()->json(['status' => true, 'message' => 'Stream stop successfully!'], 200);
            }
            else {
                return response()->json(['status' => false, 'message' => "Something went wrong!"], 404);  
            }
        } catch (Exception $ex) {
            return response()->json(['status' => false, 'message' => $ex->Message()], 404);
        }
    }

    public function streamRecord(Request $request) 
    {
        $this->logs($request->all(), 'streamRecord');

        try {
            $getRtmp = Rtmp::where('stream_key', $request->name)->first();

            if(isset($getRtmp->id) && !empty($request->path)) {
                $insertRtmpRecordData = [
                    'rtmp_id' => $getRtmp->id,
                    'recording_path' => $request->path,
                ];
                RtmpRecording::create($insertRtmpRecordData);
                // ProcessStream::dispatch($request->all())->delay(now()->addMinutes(2));
                ProcessStream::dispatch($request->all());
                return response()->json(['status' => true, 'message' => 'Stream record successfully!'], 200);
            }
            else {
                return response()->json(['status' => false, 'message' => "Something went wrong!"], 404);  
            }
        } catch (Exception $ex) {
            return response()->json(['status' => false, 'message' => $ex->Message()], 404);
        }
    }

    public function streamBlocked(Request $request) 
    {
        $this->logs($request->all(), 'streamBlocked');

        try {
            $getRtmp = Rtmp::with('rtmp_live')->where('stream_key', $request->name)->first();

            if(isset($getRtmp->id) && isset($getRtmp->rtmp_live->id)) {
                RtmpLive::where('id', $getRtmp->rtmp_live->id)->update(['status' => 0]);
                return response()->json(['status' => true, 'message' => 'Stream blocked successfully!'], 200);
            }
            else {
                return response()->json(['status' => false, 'message' => "Something went wrong!"], 404);  
            }
        } catch (Exception $ex) {
            return response()->json(['status' => false, 'message' => $ex->Message()], 404);
        }
    }

    public function shareLive($id)
    {
        try {
            $live = RtmpLive::with('rtmp')->where('status', 1)->where('rtmp_id', $id)->first();
            if(empty($live->id)) {
                abort(404);
            }
            return view("stream.live", compact('live'));
        } catch (Exception $ex) {
            return response()->json(['status' => false, 'message' => $ex->Message()], 404);
        }
    }

    public function shareRecord($id)
    {
        try {
            $record = RtmpRecording::with('rtmp')->where('status', 1)->where('id', $id)->first();
            if(empty($record->id)) {
                abort(404);
            }
            return view("stream.record", compact('record'));
        } catch (Exception $ex) {
            return response()->json(['status' => false, 'message' => $ex->Message()], 404);
        }
    }

    public function TestingAPI()
    {
        /*
        // ------------------------Recording---------------------------------
        $recData = RtmpRecording::with('rtmp')->where('status', 0)->first();
        // echo "<pre>";
        // print_r($recData->toArray());
        // die;
        $data = [
            'name' => $recData->rtmp->stream_key,
            'path' => $recData->recording_path,
        ];
        // ProcessStream::dispatch($data)->delay(now()->addMinutes(1));
        ProcessStream::dispatch($data);
        return response()->json($data, 200);
        // ------------------------Recording---------------------------------
        */

        
        // ------------------------S3 bucket---------------------------------
        $folderPath = "storage/record/IkSjJKBTpQ1UW";
        if (Storage::disk('s3')->exists($folderPath)) {
            // Storage::disk('s3')->deleteDirectory($folderPath);
            // echo "Folder '$folderPath' deleted successfully.";
        } else {
            // echo "Folder '$folderPath' does not exist.";
        }

        $folderPath = 'opt/data/hls';
        if (!Storage::disk('s3')->exists($folderPath)) {
            // Folder doesn't exist, create it
            // Storage::disk('s3')->makeDirectory($folderPath);
        }
        
        // echo Storage::disk('s3')->exists('storage/recording/yu5r05YH7Fsxx/yu5r05YH7Fsxx-1696863173-09-Oct-23-14_52_53.mp4');
        // echo Storage::disk('s3')->delete('storage/recording/yu5r05YH7Fsxx/yu5r05YH7Fsxx-1696863123-09-Oct-23-14_52_03.mp4');
        // echo Storage::disk('s3')->deleteDirectory('storage/recording/yu5r05YH7Fsxx');

        $files = Storage::disk('s3')->allFiles();
        foreach ($files as $file) {
            // Storage::disk('s3')->delete($file);
        }
       
        $folders = Storage::disk('s3')->directories();
        foreach ($folders as $folder) {
            // Storage::disk('s3')->deleteDirectory($folder);
        }

        echo "<pre>";
        print_r($files);
        die;
        // ------------------------S3 bucket---------------------------------
        

        return response()->json(['status' => true, 'message' => 'Nothing testing!'], 200);
    }

    protected function logs($req, $type) 
    {
        $logFile = public_path('logs/stream.log');
        $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $type . ' ::: ' . json_encode($req) . "\n\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        return true;
    }
}
