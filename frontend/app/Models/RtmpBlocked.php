<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RtmpBlocked extends Model
{
    use HasFactory;

    protected $table = "rtmp_blockeds";

    protected $fillable = [
        'rtmp_id',
        'blocked_datetime',
        'status',
    ];

    protected $timestamp = false;

    public function rtmp()
    {
    	return $this->hasOne(Rtmp::class, 'id', 'rtmp_id');
    }
}
