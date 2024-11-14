<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\RtmpRecording;
use App\Models\RtmpLogs;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessStream implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    public $timeout = 3600; // Job timeout set to 1 hour
    public $tries = 3; // Number of retry attempts

    public function __construct(array $data)
    {
        date_default_timezone_set(env('TIMEZONE', 'Asia/Kolkata'));
        $this->data = $data;
    }

    public function handle()
    {
        $data = $this->data;
        Log::info('Processing task with data: ', [json_encode($data)]);

        // Check if required fields are provided
        if (empty($data['name']) || empty($data['path'])) {
            return $this->logAndRespond('requiredFieldMissing', $data, "Required field missing (like name, path)");
        }

        $streamKey = $data['name'];
        $streamPath = $data['path'];
        $videoUrl = env('BACKEND_SERVER_URL') . $streamPath;
        $fileName = basename($streamPath);
        $newFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.mp4';

        // Create directory for the recording
        $streamDir = public_path('storage/record/' . $streamKey);
        if (!is_dir($streamDir)) {
            mkdir($streamDir, 0777, true);
        }

        $storagePath = public_path("storage/record/$streamKey/$fileName");
        $returnData = $this->downloadRecording($videoUrl, $storagePath);

        if (!file_exists($storagePath)) {
            return $this->logAndRespond('downloadRecording', $data, 'Downloading recording not working!');
        }

        // Ensure correct file permissions if not in local environment
        if (!App::environment('local')) {
            chmod($storagePath, 0777);
        }

        // Convert flv to mp4 if necessary (this part can be uncommented to perform conversion)
        /*
        $destinationPath = $streamDir . '/' . $newFileName;
        $exitStatus = 0;
        $command = (App::environment('local')) ? public_path('liberary/ffmpeg/bin/ffmpeg.exe') : 'ffmpeg';
        $ffmpegCommand = "{$command} -i {$storagePath} -c:v libx264 -c:a aac -strict experimental {$destinationPath}";
        exec($ffmpegCommand, $output, $exitStatus);
        */

        // If the process is successful, upload to S3 or local storage
        $destinationPath = $streamDir . '/' . $fileName;
        $s3FolderPath = 'storage/record/' . $streamKey . '/' . $fileName;

        if (!App::environment('local')) {
            return $this->uploadToS3($storagePath, $s3FolderPath, $data, $streamPath, $streamKey);
        }
        
        // For local environment, just update the database and delete temporary file
        $this->updateRecordingUrl($streamPath, $streamKey, $fileName);
        // unlink($storagePath);
        $this->deleteBackendFile($streamPath, $streamKey);
        return response()->json(['status' => true, 'message' => "Process Completed (local)."], 200);
    }

    protected function downloadRecording($url, $destinationPath)
    {
        $res = $this->callFileAPI($url, $destinationPath);
        if (!$res['status']) {
            $this->logs('cURL-downloadRecording', ['url' => $url, 'path' => $destinationPath], $res);
            throw new \Exception("Error Processing Request: " . json_encode($res));
        }
        return true;
    }

    protected function uploadToS3($storagePath, $s3FolderPath, $data, $streamPath, $streamKey)
    {
        try {
            // Ensure the S3 folder exists
            if (!Storage::disk('s3')->exists('storage/record/' . $streamKey)) {
                Storage::disk('s3')->makeDirectory('storage/record/' . $streamKey);
            }

            // Upload file to S3
            Storage::disk('s3')->put($s3FolderPath, file_get_contents($storagePath));
            $this->updateRecordingUrl($streamPath, $streamKey, basename($s3FolderPath));

            // Clean up
            unlink($storagePath);
            $this->deleteBackendFile($streamPath, $streamKey);
            return response()->json(['status' => true, 'message' => "Process Completed (S3)."], 200);
        } catch (\Aws\S3\Exception\S3Exception $e) {
            return $this->logAndRespond('S3uploadError1', $data, $e->getMessage());
        } catch (\Throwable $th) {
            return $this->logAndRespond('S3uploadError2', $data, $th->getMessage());
        }
    }

    protected function updateRecordingUrl($streamPath, $streamKey, $fileName)
    {
        RtmpRecording::where('recording_path', $streamPath)->update([
            'recording_url' => "storage/record/$streamKey/$fileName",
            'status' => 1,
        ]);
    }

    protected function deleteBackendFile($path, $name)
    {
        $url = env('VIDEO_DELETE_URL');
        $res = $this->callPostAPI($url, ['path' => $path, 'name' => $name]);
        $res = json_decode($res, true);
        
        if (!$res['status']) {
            $this->logs('cURL-deleteBackendFile', ['path' => $path, 'name' => $name], $res);
            throw new \Exception("Error Processing Request: " . json_encode($res));
        }
    }

    protected function logs($type, $req, $res)
    {
        RtmpLogs::create([
            'type' => $type,
            'payload' => json_encode($req),
            'response' => json_encode($res),
        ]);
    }

    protected function logAndRespond($type, $data, $message)
    {
        $this->logs($type, $data, ['message' => $message]);
        return response()->json(['status' => false, 'message' => $message], 404);
    }

    public function callPostAPI($url, $data)
    {
        return $this->callAPI($url, $data);
    }

    public function callFileAPI($url, $destinationPath)
    {
        return $this->callAPI($url, ['path' => $destinationPath], 'wb');
    }

    protected function callAPI($url, $data, $mode = 'r')
    {
        $ch = curl_init($url);
        $fp = $mode == 'wb' ? fopen($data['path'], 'wb') : null;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($mode == 'wb') fclose($fp);

        return $response;
    }
}
