<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RtmpComplete extends Model
{
    use HasFactory;

    protected $table = "rtmp_completes";

    protected $fillable = [
        'rtmp_id',
        'complete_datetime',
        'status',
    ];

    protected $timestamp = false;

    public function rtmp()
    {
    	return $this->hasOne(Rtmp::class, 'id', 'rtmp_id');
    }
}
