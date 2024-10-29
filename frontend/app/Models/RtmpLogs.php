<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RtmpLogs extends Model
{
    use HasFactory;

    protected $table = "rtmp_logs";

    protected $fillable = [
        'log_datetime',
        'type',
        'payload',
    ];

    protected $timestamp = false;
}
