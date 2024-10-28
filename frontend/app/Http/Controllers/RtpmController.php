<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rtmp;
use App\Models\RtmpLive;
use App\Models\CheckCopyright;
use App\Models\RtmpRecording;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stevebauman\Location\Facades\Location;
use Carbon\Carbon;
use File;
use Illuminate\Support\Facades\Storage;

class RtpmController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        date_default_timezone_set(env('TIMEZONE', 'Asia/Kolkata'));
    }

    public function home() 
    {
        return view("backend.home");
    }

    public function index(Request $request) {
        
        $type = $request->type;
        $id = $request->templeID;
        $created_by = Auth::user()->id;
        
        if($request->ajax() && !empty($request->type) && $request->type == "showTempleRecords") {

            if(!empty($id)) {
                $records = Rtmp::with('rtmp_live')->where('created_by', $created_by)->where('status', 1)->where('id', $id)->get();
                return view("backend.ajax.temple", compact('records', 'type'));
            }
            else {
                $records = Rtmp::with('rtmp_live')->where('created_by', $created_by)->where('status', 1)->orderBy('id', 'DESC')->get();
                return view("backend.ajax.temple", compact('records', 'type'));
            }
        }

        if($request->ajax() && !empty($request->type) && $request->type == "showVideosRecords") {

            if(!empty($id)) {
                $records = RtmpRecording::with('rtmp')->where('status', 1)->where('rtmp_id', $id)->get();
                return view("backend.ajax.temple", compact('records', 'type'));
            }
            else {
                $records = RtmpRecording::with('rtmp')->where('status', 1)->get();
                return view("backend.ajax.temple", compact('records', 'type'));
            }
        }

        if($request->ajax() && !empty($request->type) && $request->type == "showAllTempleNameRecords") {

            $records = Rtmp::where('created_by', $created_by)->where('status', 1)->orderBy('id', 'DESC')->get();
            return view("backend.ajax.temple", compact('records', 'type'));
        }

        if($request->ajax() && !empty($request->type) && $request->type == "getLiveStreamPageLoad") {

            if (!empty($id)) {
                $records = RtmpLive::with('rtmp')->where('rtmp_id', $id)->where('status', 1)->orderBy('id', 'DESC')->first();
                return view("backend.ajax.temple", compact('records', 'type'));
            }
            else {
                $records = RtmpLive::whereHas('rtmp', function($query) use ($created_by) {
                    $query->where('created_by', $created_by);
                })->where('status', 1)->orderBy('id', 'DESC')->first();
                return view("backend.ajax.temple", compact('records', 'type'));
            }
        }

        if($request->ajax() && !empty($request->type) && $request->type == "playVideosRecords") {

            if (!empty($id)) {
                $records = RtmpRecording::with('rtmp')->where('status', 1)->where('id', $id)->first();
                return view("backend.ajax.temple", compact('records', 'type'));
            }
            else {
                $records = RtmpRecording::with('rtmp')->where('status', 1)->orderBy('id', 'DESC')->first();
                return view("backend.ajax.temple", compact('records', 'type'));
            }
        }

        return '<b class="text-danger">Something Went Wrong.</b>';
        exit;
    }

    public function store(Request $request) {

        $timezone = $this->timezone($request);
        date_default_timezone_set($timezone);

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:rtmps,name|max:191',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false, 
                'message' => $validator->errors()->first(), 
                'error' => [$validator->errors()->first()]
            ]);          
        }

        $url = url('/');
        $exp_url = explode("/", $url);
        $host = parse_url($url, PHP_URL_HOST);

        $rtmp_url = "rtmp://".$host.":".env('RTMP_PORT')."/live";
        $stream_key = Str::random(13);
        $live_url = $exp_url[0]."//".$host.":".env('RTMP_HOST_PORT')."/hls/".$stream_key.".m3u8";

        $insert_rtmp_data = [
            'created_by' => Auth::user()->id,
            'name' => $request->name,
            'rtmp_url' => $rtmp_url,
            'stream_key' => $stream_key,
            'live_url' => $live_url,
            'status' => 1
        ];
        $insert = Rtmp::create($insert_rtmp_data);

        if (isset($insert->id)) {
            return response()->json([
                'status' => true,
                'message' => 'Stream created successfully.',
                'result' => $insert
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'result' => $insert
            ]);
        }
    }

    public function destroy(Request $request, $id) {

        $get_rtmp = Rtmp::where(['id' => $id, 'status' => 1])->first();

        if(empty($get_rtmp)) {
            return response()->json([
                'status' => false,
                'message' => 'Rtmp not found!!!.',
                'result' => $id
            ]);
        }

        $folder_path = public_path("storage/copyright/".$get_rtmp->stream_key);
        if (is_dir($folder_path)) {
            array_map('unlink', glob("$folder_path/*.*"));
            rmdir($folder_path);    
        }

        if ($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_NAME'] == 'localhost') {
            $folder_path = public_path("storage/recording/".$get_rtmp->stream_key);
            if (is_dir($folder_path)) {
                array_map('unlink', glob("$folder_path/*.*"));
                rmdir($folder_path);    
            }
        } else {
            // Replace with the S3 folder path
            $folder_path = "storage/recording/".$get_rtmp->stream_key;
            // List all files in the folder
            $files = Storage::disk('s3')->files($folder_path);
            // Delete each file in the folder
            foreach ($files as $file) {
                Storage::disk('s3')->delete($file);
            }
            Storage::disk('s3')->deleteDirectory($folder_path);
        }

        Rtmp::where('id', $id)->delete();
        RtmpLive::where('rtmp_id', $id)->delete();
        RtmpLive::where('rtmp_id', $id)->delete();
        CheckCopyright::where('rtmp_id', $id)->delete();
        RtmpRecording::where('rtmp_id', $id)->delete();

        if (isset($get_rtmp)) {
            return response()->json([
                'status' => true,
                'message' => 'Rtmp deleted successfully.',
                'result' => $folder_path
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'result' => $folder_path
            ]);
        }
    }

    public function shows(Request $request, $id) {

        $id = base64_decode($id);
        $created_by = Auth::user()->id;
        $showAllTempleNameRecords = Rtmp::select('rtmps.*')
                    ->join('rtmp_recordings', 'rtmp_recordings.rtmp_id', '=', 'rtmps.id')
                    ->where('rtmps.created_by', $created_by)->where('rtmps.status', 1)
                    ->groupBy('rtmp_recordings.rtmp_id')
                    ->orderBy('rtmps.id', 'DESC')
                    ->get();

        $records = RtmpRecording::with('rtmp')->where('status', 1)->where('id', $id)->first();

        if(empty($records->id)) {
            abort(404);
        } 
        else {
            $records_all = RtmpRecording::where('status', 1)->where('rtmp_id', $records->rtmp_id)->get();
            return view("backend.show", compact('showAllTempleNameRecords', 'records', 'records_all', 'id'));
        }
    }

    public function delete(Request $request, $id) {

        $get_rtmp_recording = RtmpRecording::with('rtmp')->where('id', $id)->first();
        if(empty($get_rtmp_recording)) {
            return response()->json([
                'status' => false,
                'message' => 'Video not found!!!.',
                'result' => $id
            ]);
        }

        $delete = RtmpRecording::where('id', $id)->delete();
        $count = RtmpRecording::where(['rtmp_id' => $get_rtmp_recording->rtmp_id, 'status' => 1])->get()->count();

        if ($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_NAME'] == 'localhost') {
            $file_path = public_path($get_rtmp_recording->recording_url);
            $folder_path = public_path("storage/recording/".$get_rtmp_recording->rtmp->stream_key);
    
            if (File::exists($file_path)) {
                // File::delete($file_path);
                unlink($file_path);
            }
            if($count == 0) {
                rmdir($folder_path);
            }
        } else {
            $file_path = $get_rtmp_recording->recording_url;
            $folder_path = "storage/recording/".$get_rtmp_recording->rtmp->stream_key;

            if (Storage::disk('s3')->exists($file_path)) {
                Storage::disk('s3')->delete($file_path);
            }
            if($count == 0) {
                Storage::disk('s3')->deleteDirectory($folder_path);
            }
        }

        if (isset($delete)) {
            return response()->json([
                'status' => true,
                'message' => 'Video deleted successfully.',
                'result' => $count
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'result' => $count
            ]);
        }
    }

    public function videos(Request $request, $stream_key) {

        $created_by = Auth::user()->id;
        $showAllTempleNameRecords = Rtmp::where('created_by', $created_by)->where('status', 1)->orderBy('id', 'DESC')->get();
        // $records = Rtmp::with('rtmp_recording')->where('stream_key', $stream_key)->where('status', 1)->first();
        $records = Rtmp::whereHas('rtmp_recording', function($query) {
            $query->where('status', 1);
        })->where('stream_key', $stream_key)->where('status', 1)->first();
        
        if(empty($records->id)) {
            abort(404);
        } 
        else {
            return view("backend.video", compact('showAllTempleNameRecords', 'records', 'stream_key'));
        }
    }

    public function unblock(Request $request) {
        $data = RtmpLive::where('rtmp_id', $request->id)->delete();
        return response()->json([], 200);
    }

    public function timezone(Request $request) {

        $ip = $request->ip();
        $get_country_data = Location::get($ip);
        if(empty($get_country_data->timezone)) {
            $timezone = "Asia/Kolkata";
        } else {
            $timezone = $get_country_data->timezone;
        }
        return $timezone;
    }
}
