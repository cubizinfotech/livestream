<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rtmp;
use App\Models\RtmpLive;
use App\Models\RtmpRecording;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Jobs\CreateRTMP;
use App\Jobs\DeleteRtmp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

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

    public function index(Request $request)
    {
        $type = $request->type;
        $id = $request->templeID;
        $created_by = Auth::user()->id;
        $records = null;

        if ($request->ajax()) {
            switch ($type) {
                case "showTempleRecords":
                    $records = !empty($id) 
                        ? Rtmp::with('rtmp_live')->where('created_by', $created_by)->whereIn('status', [1, 2])->where('id', $id)->get()
                        : Rtmp::with('rtmp_live')->where('created_by', $created_by)->whereIn('status', [1, 2])->orderBy('id', 'DESC')->get();
                    break;
                case "showVideosRecords":
                    $records = !empty($id)
                        ? RtmpRecording::with('rtmp')->where('status', 1)->where('rtmp_id', $id)->get()
                        : RtmpRecording::with('rtmp')->where('status', 1)->get();
                    break;
                case "showAllTempleNameRecords":
                    $records = Rtmp::where('created_by', $created_by)->where('status', 1)->orderBy('id', 'DESC')->get();
                    break;
                case "getLiveStreamPageLoad":
                    $records = !empty($id)
                        ? Rtmp::with('rtmp_live')->where('id', $id)->where('status', 1)->orderBy('id', 'DESC')->first()
                        : Rtmp::whereHas('rtmp_live')->where('status', 1)->where('created_by', $created_by)->orderBy('id', 'DESC')->first();
                    /*
                    $records = !empty($id)
                        ? Rtmp::whereHas('rtmp_live', function($query) {
                            return $query->where('status', 1);
                        })->where('id', $id)->where('status', 1)->orderBy('id', 'DESC')->first()
                        : Rtmp::whereHas('rtmp_live', function($query) {
                            return $query->where('status', 1);
                        })->where('status', 1)->orderBy('id', 'DESC')->first();
                    */
                    break;
                case "playVideosRecords":
                    $records = !empty($id)
                        ? RtmpRecording::with('rtmp')->where('status', 1)->where('id', $id)->first()
                        : RtmpRecording::with('rtmp')->where('status', 1)->orderBy('id', 'DESC')->first();
                    break;
                default:
                    return '<b class="text-danger">Something Went Wrong.</b>';
            }
            return view("backend.ajax.temple", compact('records', 'type'));
        }

        return '<b class="text-danger">Something Went Wrong.</b>';
    }

    public function store(Request $request)
    {
        $userId = Auth::user()->id;
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:191', Rule::unique('rtmps')->where(fn($query) => $query->where('created_by', $userId))],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'error' => [$validator->errors()->first()]]);
        }

        $url = url('/');
        $exp_url = explode("/", $url);
        $host = parse_url($url, PHP_URL_HOST);
        $server_name = preg_replace('/[^A-Za-z0-9]/', '', ucwords($request->name));
        $rtmp_port = $this->generateUniquePort();
        $http_port = $this->generateUniquePort();
        $stream_key = Str::random(13);
        $rtmp_url = "rtmp://{$host}:{$rtmp_port}/live";
        $live_url = "{$exp_url[0]}//{$host}:{$http_port}/hls/{$stream_key}.m3u8";

        $rtmpDdata = [
            'created_by' => $userId,
            'name' => $request->name,
            'rtmp_url' => $rtmp_url,
            'stream_key' => $stream_key,
            'live_url' => $live_url,
            'server_name' => "S{$stream_key}-{$server_name}",
            'container_name' => "C{$stream_key}-{$server_name}",
            'rtmp_port' => $rtmp_port,
            'http_port' => $http_port,
            'status' => 2
        ];

        $rtmpIinsert = Rtmp::create($rtmpDdata);

        if ($rtmpIinsert) {
            CreateRTMP::dispatch($rtmpIinsert->toArray());
            return response()->json(['status' => true, 'message' => 'Stream created successfully.', 'result' => $rtmpIinsert]);
        }

        return response()->json(['status' => false, 'message' => 'Something went wrong.']);
    }

    public function destroy(Request $request, $id)
    {
        $getRtmp = Rtmp::where(['id' => $id])->first();

        if (!$getRtmp) {
            return response()->json(['status' => false, 'message' => 'Rtmp not found!!!.', 'result' => $id]);
        }

        $folderName = $getRtmp->stream_key;
        $folderPath = public_path("storage/record/{$folderName}");

        if (App::environment('local')) {
            if (is_dir($folderPath)) {
                array_map('unlink', glob("{$folderPath}/*.*"));
                rmdir($folderPath);
            }
        } else {
            try {
                if (is_dir($folderPath)) {
                    foreach (glob("{$folderPath}/*.*") as $file) {
                        if (is_file($file)) {
                            unlink($file);
                        }
                    }
                    rmdir($folderPath);
                }
                $folderPath = "storage/record/{$folderName}";
                if (Storage::disk('s3')->exists($folderPath)) {
                    $files = Storage::disk('s3')->files($folderPath);
                    foreach ($files as $file) {
                        Storage::disk('s3')->delete($file);
                    }
                    Storage::disk('s3')->deleteDirectory($folderPath);
                }
            } catch (\Throwable $e) {
                return response()->json(['status' => false, 'message' => "S3 file error", 'error' => $e->getMessage()]);
            }
        }

        Rtmp::where('id', $id)->update(['status' => 0]);
        RtmpLive::where('rtmp_id', $id)->delete();
        RtmpRecording::where('rtmp_id', $id)->delete();

        DeleteRtmp::dispatch($getRtmp->toArray());
        return response()->json(['status' => true, 'message' => 'Rtmp deleted successfully.']);
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
            catch (\Aws\S3\Exception\S3Exception $e) {
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
        $records = Rtmp::where('stream_key', $stream_key)->where('status', 1)
                    ->with(['rtmp_recording' => function($query) {
                        $query->where('status', 1);
                    }])->first();
        
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

    public function generateUniquePort()
    {
        do {
            $port = rand(7001, 8099);
            $exists = DB::table('rtmps')->where('rtmp_port', $port)->orWhere('http_port', $port)->exists();
        } while ($exists);

        return $port;
    }
}
