<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\RtmpRecording;
use App\Models\RtmpLogs;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class ProcessStream implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    public $timeout = 3600; // Allows the job to run for up to 1 hour (3600 seconds).
    public $tries = 3; // Number of retry attempts

    /**
     * Create a new job instance.
     *
     * @param mixed $data
     */
    public function __construct(array $data)
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

        if (!empty($data['name']) && !empty($data['path'])) {

            $streamKey = $data['name'];
            $streamPath = $data['path'];

            $videoUrl = env('BACKEND_SERVER_URL').$streamPath;
            $fileName = explode("/", $streamPath)[1];
            $fileNameExplode = explode(".", $fileName);
            $newFileName = $fileNameExplode[0].'.mp4';

            // Create directoty
            $streamDir = public_path('storage/record/'.$streamKey);
            if (!is_dir($streamDir)) {
                mkdir($streamDir);     
            }

            $storagePath = public_path("storage/record/$streamKey/$fileName");

            $returnData = $this->downloadRecording($videoUrl, $storagePath);
            if (file_exists($storagePath)) {

                if (!App::environment('local')) {
                    chmod($storagePath, 0777);
                }

                // Record folder path
                $destinationPath = $streamDir . '/' . $newFileName;
                
                // START ffmpeg convert flv to mp4 
                $exitStatus = 0;
                /*
                if (!App::environment('local')) {
                    $command = 'ffmpeg';
                } else {
                    $command = public_path('liberary/ffmpeg/bin/ffmpeg.exe');
                }
                $ffmpegCommand = "{$command} -i {$storagePath} -c:v libx264 -c:a aac -strict experimental {$destinationPath}";
                if (!file_exists($destinationPath)) {
                    exec($ffmpegCommand, $output, $exitStatus);
                }
                */
                // END ffmpeg convert flv to mp4

                if ($exitStatus == 0) {

                    /*
                    if (!App::environment('local')) {
                        chmod($destinationPath, 0777);
                    }
                    */

                    // if ($_SERVER['SERVER_ADDR'] != '127.0.0.1' && $_SERVER['SERVER_NAME'] != 'localhost') {
                    if (!App::environment('local')) {
                        
                        // File upload from S3 bucket
                        try {
                            $folderPath = 'storage/record/'.$streamKey;
                            if (!Storage::disk('s3')->exists($folderPath)) {
                                Storage::disk('s3')->makeDirectory($folderPath);
                            }
                
                            // S3 folder path
                            // $s3FolderPath = $folderPath.'/'.$newFileName;
                            $s3FolderPath = $folderPath.'/'.$fileName;
                
                            // Storage::disk('s3')->put($s3FolderPath, file_get_contents($destinationPath));
                            Storage::disk('s3')->put($s3FolderPath, file_get_contents($storagePath));
                            $s3FileUrl = Storage::disk('s3')->url($s3FolderPath);

                            $updateData = [
                                // 'recording_url' => "storage/record/$streamKey/$newFileName",
                                'recording_url' => "storage/record/$streamKey/$fileName",
                                'status' => 1,
                            ];
                            RtmpRecording::where('recording_path', $streamPath)->update($updateData);
                            unlink($storagePath);
                            // unlink($destinationPath);
                            $this->deleteBackendFile($streamPath, $streamKey);
                            return response()->json(['status' => true, 'message' => "Process Completed (S3)."], 200);
                        }
                        catch (Aws\S3\Exception\S3Exception $e) {
                            $logData = [
                                'message' => $e->getMessage(),
                            ];
                            $this->logs('S3uploadError1', $data, $logData);
                            return response()->json(['status' => false, 'message' => "S3 file upload error 1!"], 404);
                        } 
                        catch (\Throwable $th) {
                            $logData = [
                                'message' => $th->getMessage(),
                            ];
                            $this->logs('S3uploadError2', $data, $logData);
                            return response()->json(['status' => false, 'message' => "S3 file upload error 2!"], 404);
                        }
                    } 
                    else {
                        $updateData = [
                            // 'recording_url' => "storage/record/$streamKey/$newFileName",
                            'recording_url' => "storage/record/$streamKey/$fileName",
                            'status' => 1,
                        ];
                        RtmpRecording::where('recording_path', $streamPath)->update($updateData);
                        unlink($storagePath);
                        $this->deleteBackendFile($streamPath, $streamKey);
                        return response()->json(['status' => true, 'message' => "Process Completed (local)."], 200);
                    }
                } else {
                    $logData = [
                        'command' => $ffmpegCommand,
                        'status' => $exitStatus,
                        'inputFile' => $storagePath,
                        'outputFile' => $destinationPath,
                    ];
                    $this->logs('ffmpegCommand', $data, $logData);
                    return response()->json(['status' => false, 'message' => "ffmpeg command not working!"], 404);
                }
            } 
            else {
                $logData = [
                    'message' => 'Downloading recording not working!',
                ];
                $this->logs('downloadRecording', $data, $logData);
                return response()->json(['status' => false, 'message' => "Temporary downloading not working!"], 404);
            }
        }
        else {
            $logData = [
                'message' => 'Required field missing (like name, path)',
            ];
            $this->logs('requiredFieldMissing', $data, $logData);
            return response()->json(['status' => false, 'message' => "Something went wrong!"], 404);
        }
    }

    protected function downloadRecording($url, $destinationPath)
    {
        $data = [
            'url' => $url,
            'path' => $destinationPath,
        ];

        $res = $this->callFileAPI($url, $destinationPath);
        if ($res['status'] == false) {
            $this->logs('cURL-downloadRecording', $data, $res);
            throw new Exception("Error Processing Request: " . json_encode($res));
        } else {
            return true;
        }
    }

    protected function deleteBackendFile($path, $name)
    {
        $url = env('BACKEND_SERVER_URL').'api_record.php';
        $data = [
            'path' => $path,
            'name' => $name,
        ];

        $res = $this->callPostAPI($url, $data);
        if ($res['status'] == false) {
            $this->logs('cURL-deleteBackendFile', $data, $res);
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

    public function callFileAPI($url, $destinationPath)
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

        // Close resources
        curl_close($ch);
        fclose($fp);

        return $res;
    }
}

/*

*/
