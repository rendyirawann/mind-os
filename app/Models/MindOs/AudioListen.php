<?php

namespace App\Models\MindOs;

use Illuminate\Database\Eloquent\Model;

class AudioListen extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'audio_track_id', 'listened_at'];

    protected $casts = ['listened_at' => 'datetime'];
}
