<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rtmp;
use App\Models\RtmpLive;
use App\Models\RtmpRecording;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stevebauman\Location\Facades\Location;
use Carbon\Carbon;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use App\Jobs\CreateRTMP;

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

        $stream_key = Str::random(13);
        $rtmp_url = "rtmp://".$host.":".env('RTMP_PORT')."/live_$stream_key";
        $live_url = $exp_url[0]."//".$host.":".env('RTMP_HOST_PORT')."/hls/".$stream_key.".m3u8";

        $rtmpDdata = [
            'created_by' => Auth::user()->id,
            'name' => $request->name,
            'rtmp_url' => $rtmp_url,
            'stream_key' => $stream_key,
            'live_url' => $live_url,
            'status' => 1
        ];
        $insert = Rtmp::create($rtmpDdata);

        if (isset($insert->id)) {

            // CreateRTMP::dispatch($rtmpDdata)->delay(now()->addMinutes(2));
            CreateRTMP::dispatch($rtmpDdata);
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

        $getRtmp = Rtmp::where(['id' => $id, 'status' => 1])->first();

        if(empty($getRtmp)) {
            return response()->json([
                'status' => false,
                'message' => 'Rtmp not found!!!.',
                'result' => $id
            ]);
        }

        $folderName = $getRtmp->stream_key;
        $folderPath = public_path("storage/record/$folderName");

        // if ($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_NAME'] == 'localhost') {
        if (App::environment('local')) {
            if (is_dir($folderPath)) {
                array_map('unlink', glob("$folderPath/*.*"));
                rmdir($folderPath);
            }
        } 
        else {
            try {
                if (is_dir($folderPath)) {
                    rmdir($folderPath);
                }
                $folderPath = "storage/record/$folderName";
                if(Storage::disk('s3')->exists($folderPath)) {
                    $files = Storage::disk('s3')->files($folderPath);
                    foreach ($files as $file) {
                        Storage::disk('s3')->delete($file);
                    }
                    Storage::disk('s3')->deleteDirectory($folderPath);
                }
            }
            catch (Aws\S3\Exception\S3Exception $e) {
                return response()->json(['status' => false, 'message' => "S3 file upload error 1!", 'message2' => $e->getMessage()], 404);
            } 
            catch (\Throwable $th) {
                return response()->json(['status' => false, 'message' => "S3 file upload error 2!", 'message2' => $th->getMessage()], 404);
            }
        }

        Rtmp::where('id', $id)->delete();
        RtmpLive::where('rtmp_id', $id)->delete();
        RtmpRecording::where('rtmp_id', $id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Rtmp deleted successfully.',
            'result' => $folderPath
        ]);
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

        $rtmpRecording = RtmpRecording::with('rtmp')->where('id', $id)->first();
        if(empty($rtmpRecording)) {
            return response()->json([
                'status' => false,
                'message' => 'Video not found!!!.',
                'result' => $id
            ]);
        }

        $folderName = $rtmpRecording->rtmp->stream_key;
        $folderPath = public_path("storage/record/$folderName");
        $count = RtmpRecording::where(['rtmp_id' => $rtmpRecording->rtmp_id, 'status' => 1])->get()->count();

        // if ($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_NAME'] == 'localhost') {
        if (App::environment('local')) {
            $filePath = public_path($rtmpRecording->recording_url);
            if (File::exists($filePath)) {
                // File::delete($filePath);
                unlink($filePath);
            }
        } else {
            try {
                if (Storage::disk('s3')->exists($rtmpRecording->recording_url)) {
                    Storage::disk('s3')->delete($rtmpRecording->recording_url);
                }
                if($count == 1) {
                    Storage::disk('s3')->deleteDirectory("storage/record/$folderName");
                }
            }
            catch (Aws\S3\Exception\S3Exception $e) {
                return response()->json(['status' => false, 'message' => "S3 file upload error 1!", 'message2' => $e->getMessage()], 404);
            } 
            catch (\Throwable $th) {
                return response()->json(['status' => false, 'message' => "S3 file upload error 2!", 'message2' => $th->getMessage()], 404);
            }
        }

        RtmpRecording::where('id', $id)->delete();
        if($count == 1) {
            rmdir($folderPath);
        }
        return response()->json([
            'status' => true,
            'message' => 'Video deleted successfully.',
            'result' => $count
        ]);
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
