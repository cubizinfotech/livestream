<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Rtmp;

class RtmpLive extends Model
{
    use HasFactory;

    protected $table = "rtmp_lives";

    protected $fillable = [
        'rtmp_id',
        'ip_address',
        'timezone',
        'datetime',
        'status',
    ];

    protected $timestamp = false;

    public function rtmp()
    {
    	return $this->hasOne(Rtmp::class, 'id', 'rtmp_id');
    }
}
