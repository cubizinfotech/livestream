<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Rtmp;

class RtmpRecording extends Model
{
    use HasFactory;

    protected $table = "rtmp_recordings";

    protected $fillable = [
        'rtmp_id',
        'recording_url',
        'recording_path',
        'status',
    ];

    protected $timestamp = false;

    public function rtmp()
    {
    	return $this->hasOne(Rtmp::class, 'id', 'rtmp_id');
    }
}
