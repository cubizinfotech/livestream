<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Rtmp;
use App\Models\RtmpLive;
use App\Models\RtmpRecording;
use Carbon\Carbon;

class ProcessTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    /**
     * Create a new job instance.
     *
     * @param mixed $data
     */
    public function __construct($data)
    {
        date_default_timezone_set(env('TIMEZONE'));
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

        if (isset($data['rtmp_id']) && isset($data['rtmp_key'])) {
            if ($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_NAME'] == 'localhost') {
                $returnData = $this->recording($data['rtmp_key']);
            } else {
                $returnData = $this->recordingS3Bucket($data['rtmp_key']);
            }
        } else {
            $returnData = $this->checkingStream();
        }

        if ($returnData['status']) {
            return response()->json(['status' => false, 'message' => "Something went wrong!"], 404);
        } else {
            return response()->json(['status' => true, 'message' => 'Successfully!'], 200);
        }
    }

    protected function checkingStream()
    {
        $rtmpLive = RtmpLive::where('status', 1)->get();

        if(count($rtmpLive->toArray()) == 0) {
            $return_data = [
                'status' => true,
                'message' => 'No stream found!',
            ];
            return $return_data;
        }

        foreach ($rtmpLive as $key => $value) {
            $streamStartDatetime = $value->streaming_datetime;
            $currentDateTime = date("Y-m-d H:i:s");
            $maxStreamTime = env('MAX_STREAM_TIME', 90);
        }
    }

    protected function recordingS3Bucket(string $streamKey)
    {
        $return_data = [];

        // $directory = public_path('live_stream/recording');
        $directory = '/mnt/streaming/recording';
        $pattern = $streamKey . '*.flv';
        // $files = scandir($directory);
        // $files = glob($directory . '/' . $pattern);
        $files = array_map('basename', glob($directory . '/' . $pattern, GLOB_BRACE));
        if (count($files) < 1) {
            $return_data = [
                'status' => false,
                'message' => 'No files found in live-stream recording folder.',
            ];
            return $return_data;
        }
        
        $temple_directory = public_path('storage/recording/'.$streamKey.'');
        if (!is_dir($temple_directory)) {
            mkdir($temple_directory);     
        }

        // START ffmpeg convert flv to mp4 
        $fileName = $files[0];
        $explodeFileName = explode('.', $fileName);
        // $inputFile = public_path('live_stream/recording/'.$fileName);
        $inputFile = '/mnt/streaming/recording/'.$fileName;
        $outputFile = public_path('storage/recording/'.$streamKey.'/'.$explodeFileName[0].'.mp4');
        $command = "ffmpeg";

        $ffmpegCommand = "{$command} -i {$inputFile} -c:v libx264 -c:a aac -strict experimental {$outputFile}";
        exec($ffmpegCommand, $output, $exitStatus);

        $logType = "FFMPEG";
        $logData = [
            'type' => 'ffmpeg',
            'command' => $ffmpegCommand,
            'status' => $exitStatus,
        ];
        logDatas($logType, $logData);

        if ($exitStatus !== 0) {
            $return_data = [
                'status' => false,
                'message' => "FFmpeg command encountered an error. Exit status: $exitStatus",
            ];
            return $return_data;
        }
        // END ffmpeg convert flv to mp4

        try {
            $folderPath = 'storage/recording/'.$streamKey;
            if (!Storage::disk('s3')->exists($folderPath)) {
                // Folder doesn't exist, create it
                Storage::disk('s3')->makeDirectory($folderPath);
            }

            $file_name = $explodeFileName[0].'.mp4'; // File name
            $localFilePath = $outputFile; // Local file path
            $s3FolderPath = $folderPath.'/'.$file_name; // S3 folder path

            Storage::disk('s3')->put($s3FolderPath, file_get_contents($localFilePath));
            $s3FileUrl = Storage::disk('s3')->url($s3FolderPath);

            $insert_rtmp_recording_data = [
                'rtmp_id' => $get_rtmp->id,
                'recording_url' => $s3FolderPath
            ];
            $insert = RtmpRecording::create($insert_rtmp_recording_data);
            if (!isset($insert->id) || empty($insert->id)) {
                $return_data = [
                    'status' => false,
                    'message' => 'Insert failed in database RtmpRecording.',
                ];
                return $return_data;
            }

            // Optionally, delete the local file after moving it to S3
            unlink($inputFile);
            unlink($outputFile);
            $return_data = [
                'status' => true,
                'insertedID' => $insert->id,
                'count_file' => count($files),
            ];
            return $return_data;
            exit;
        }
        catch (Aws\S3\Exception\S3Exception $e) {
            $return_data = [
                'status' => false,
                'message' => $e->getMessage(),
            ];
            return $return_data;
            // throw $th; 
            // echo $api_error = $e->getMessage(); 
        } 
        catch (\Throwable $th) {
            $return_data = [
                'status' => false,
                'message' => $th->getMessage(),
            ];
            return $return_data;
            // throw $th;
            // echo $api_error = $th->getMessage(); 
        }
    }

    protected function recording(string $streamKey)
    {
        $return_data = [];

        $directory = public_path('live_stream/recording');
        $pattern = $streamKey . '*.flv';
        // $files = scandir($directory);
        // $files = glob($directory . '/' . $pattern);
        $files = array_map('basename', glob($directory . '/' . $pattern, GLOB_BRACE));
        if (count($files) < 1) {
            $return_data = [
                'status' => false,
                'message' => 'No files found in live-stream recording folder.',
            ];
            return $return_data;
        }

        $temple_directory = public_path('storage/recording/'.$streamKey.'');
        if (!is_dir($temple_directory)) {
            mkdir($temple_directory);     
        }

        foreach($files as $key => $value) {

            $sourcePath = $directory . '/' . $value;
            $destinationPath = $temple_directory . '/' . $value;

            if (rename($sourcePath, $destinationPath)) {

                $destinationPath = "storage/recording/".$streamKey."/".$value;
                $insert_rtmp_recording_data = [
                    'rtmp_id' => $get_rtmp->id,
                    'recording_url' => $destinationPath
                ];
                $insert = RtmpRecording::create($insert_rtmp_recording_data);
                if (!isset($insert->id) || empty($insert->id)) {
                    $return_data = [
                        'status' => false,
                        'message' => 'Insert failed in database RtmpRecording.',
                    ];
                    return $return_data;
                }

                $return_data[] = [
                    'status' => true,
                    'insertedID' => $insert->id,
                    'count_file' => count($files),
                ];
            } else {
                $return_data[] = [
                    'status' => false,
                    'message' => 'Failed to move the file.',
                ];
            }
        }
        return $return_data;
    }
}
