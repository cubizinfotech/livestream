<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rtmp;
use App\Models\RtmpLive;
use App\Models\RtmpComplete;
use App\Models\RtmpRecording;
use App\Models\RtmpBlocked;
use App\Models\RtmpLogs;
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

            if(isset($getRtmp->id) && isset($getRtmp->rtmp_live->id) && $getRtmp->rtmp_live->status == 0) {
                return response()->json(['status' => false, 'message' => "Blocked the stream!"], 404);
            }
            else if(isset($getRtmp->id)) {
                $insertRtmpLiveData = [
                    'rtmp_id' => $getRtmp->id,
                    'streaming_datetime' => date("Y-m-d H:i:s"),
                    'status' => 1
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
                $insertRtmpCompleteData = [
                    'rtmp_id' => $getRtmp->id,
                    'complete_datetime' => date("Y-m-d H:i:s"),
                    'status' => 1
                ];
                RtmpComplete::create($insertRtmpCompleteData);
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
                    'recording_datetime' => date("Y-m-d H:i:s"),
                    'recording_path' => $request->path,
                    'status' => 0
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
                $insertRtmpBlockedData = [
                    'rtmp_id' => $getRtmp->id,
                    'blocked_datetime' => date("Y-m-d H:i:s"),
                    'status' => 1
                ];
                RtmpBlocked::create($insertRtmpBlockedData);
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
        $this->logs([$id], 'shareLive');

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
        $this->logs([$id], 'shareRecord');

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
        $recData = RtmpRecording::where('status', 0)->first();
        // echo "<pre>";
        // print_r($recData);
        // die;
        $data = [
            'name' => 'IkSjJKBTpQ1UW',
            'path' => $recData->recording_path,
        ];
        // $this->temp_handle($data);
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
        $insertRtmpLogData = [
            'log_datetime' => date("Y-m-d H:i:s"),
            'type' => $type,
            'payload' => json_encode($req),
        ];
        RtmpLogs::create($insertRtmpLogData);

        $logFile = public_path('logs/stream.log');
        $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $type . ' ::: ' . json_encode($req) . "\n\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        return true;
    }

    protected function temp_handle($data)
    {
        \Log::info('Processing task with data: ', [json_encode($data)]);

        if (!empty($data['name']) && !empty($data['path'])) {

            $streamKey = $data['name'];
            $streamPath = $data['path'];

            $videoUrl = env('BACKEND_SERVER_URL').$streamPath;
            $fileName = explode("/", $streamPath)[1];
            $fileNameExplode = explode(".", $fileName);
            $newFileName = $fileNameExplode[0].'.mp4';
            $storagePath = public_path("storage/temp_rec/$fileName");

            $returnData = $this->downloadTempRecord($videoUrl, $storagePath);
            sleep(1);
            if (file_exists($storagePath)) {

                if (!App::environment('local')) {
                    chmod($storagePath, 0777);
                }

                // Create RTMP directoty
                $streamDir = public_path('storage/record/'.$streamKey);
                if (!is_dir($streamDir)) {
                    mkdir($streamDir);     
                }

                $destinationPath = $streamDir . '/' . $newFileName; // Record folder path
                
                // START ffmpeg convert flv to mp4 
                $exitStatus = 0;
                if (!App::environment('local')) {
                    $command = 'ffmpeg';
                } else {
                    $command = public_path('liberary/ffmpeg/bin/ffmpeg.exe');
                }
                $ffmpegCommand = "{$command} -i {$storagePath} -c:v libx264 -c:a aac -strict experimental {$destinationPath}";
                if (!file_exists($destinationPath)) {
                    exec($ffmpegCommand, $output, $exitStatus);
                }
                // END ffmpeg convert flv to mp4

                if ($exitStatus == 0) {

                    if (!App::environment('local')) {
                        chmod($destinationPath, 0777);
                    }

                    // if ($_SERVER['SERVER_ADDR'] != '127.0.0.1' && $_SERVER['SERVER_NAME'] != 'localhost') {
                    if (!App::environment('local')) {
                        
                        // File upload from S3 bucket
                        try {
                            $folderPath = 'storage/record/'.$streamKey;
                            if (!Storage::disk('s3')->exists($folderPath)) {
                                Storage::disk('s3')->makeDirectory($folderPath);
                            }
                
                            $s3FolderPath = $folderPath.'/'.$newFileName; // S3 folder path
                
                            Storage::disk('s3')->put($s3FolderPath, file_get_contents($destinationPath));
                            $s3FileUrl = Storage::disk('s3')->url($s3FolderPath);

                            $updateData = [
                                'recording_url' => "storage/record/$streamKey/$newFileName",
                                'status' => 1,
                            ];
                            RtmpRecording::where('recording_path', $streamPath)->update($updateData);
                            unlink($storagePath);
                            unlink($destinationPath);
                            $this->deleteBackendFile($streamPath);
                            return response()->json(['status' => true, 'message' => "Process Completed (S3)."], 200);
                        }
                        catch (Aws\S3\Exception\S3Exception $e) {
                            $logData = [
                                'message' => $e->getMessage(),
                            ];
                            $this->logs($logData, 'S3uploadError1');
                            return response()->json(['status' => false, 'message' => "S3 file upload error 1!"], 404);
                        } 
                        catch (\Throwable $th) {
                            $logData = [
                                'message' => $th->getMessage(),
                            ];
                            $this->logs($logData, 'S3uploadError2');
                            return response()->json(['status' => false, 'message' => "S3 file upload error 2!"], 404);
                        }
                    } 
                    else {
                        $updateData = [
                            'recording_url' => "storage/record/$streamKey/$newFileName",
                            'status' => 1,
                        ];
                        RtmpRecording::where('recording_path', $streamPath)->update($updateData);
                        unlink($storagePath);
                        $this->deleteBackendFile($streamPath);
                        return response()->json(['status' => true, 'message' => "Process Completed (local)."], 200);
                    }
                } else {
                    $logData = [
                        'type' => 'ffmpeg command',
                        'command' => $ffmpegCommand,
                        'status' => $exitStatus,
                        'inputFile' => $storagePath,
                        'outputFile' => $destinationPath,
                    ];
                    $this->logs($logData, 'ffmpeg');
                    return response()->json(['status' => false, 'message' => "ffmpeg command not working!"], 404);
                }
            } 
            else {
                $logData = [
                    'message' => 'Temporary downloading not working!',
                ];
                $this->logs($logData, 'tempDownload');
                return response()->json(['status' => false, 'message' => "Temporary downloading not working!"], 404);
            }
        }
        else {
            $logData = [
                'message' => 'Required field missing (like name, path)',
            ];
            $this->logs($logData, 'requiredField');
            return response()->json(['status' => false, 'message' => "Something went wrong!"], 404);
        }
    }

    protected function downloadTempRecord($url, $destinationPath)
    {
        $ch = curl_init($url);
        $fp = fopen($destinationPath, 'wb');

        // Set Curl options for large file transfer
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0); // Set timeout to 0 for no timeout
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 8192); // Set buffer size to 8KB
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification

        // Execute the request
        curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            $logData = [
                'message' => "cURL Error: $error_msg",
            ];
            $this->logs($logData, 'downloadTempRecord');
        } else {
            // echo "File downloaded successfully.";
        }

        // Close resources
        curl_close($ch);
        fclose($fp);

        return true;
    }

    protected function deleteBackendFile($path)
    {
        $url = env('BACKEND_SERVER_URL').'api.php';
        $data = [
            'path' => $path,
        ];

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
            $error_msg = curl_error($ch);
            $logData = [
                'message' => "cURL Error: $error_msg",
            ];
            $this->logs($logData, 'deleteBackendFile');
        } else {
            // echo "Response from API: $response";
        }

        // Close the cURL session
        curl_close($ch);

        return true;
    }
}
