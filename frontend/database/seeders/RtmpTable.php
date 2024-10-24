<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RtmpTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rtmps')->insert([
            [
                'created_by' => 1, 
                'name' => 'RTMP-test', 
                'rtmp_url' => 'rtmp://localhost:1919/live',
                'stream_key' => 'stream',
                'live_url' => 'http://localhost:8989/hls/stream.m3u8',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
