<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'from_id',
        'to_id',
        'message',
        'voice_message',
        'is_read',
        'temp_image',
        'created_at',
    ];

    protected $appends = ['from', 'to', 'from_image', 'to_image', 'time'];

    public function getFromAttribute()
    {
    	return @User::where('id', $this->attributes['from_id'])->pluck('name')->first();
    }

    public function getToAttribute()
    {
    	return @User::where('id', $this->attributes['to_id'])->pluck('name')->first();
    }

    public function getFromImageAttribute()
    {
        return @User::where('id', $this->attributes['from_id'])->pluck('image')->first();
    }

    public function getToImageAttribute()
    {
        return @User::where('id', $this->attributes['to_id'])->pluck('image')->first();
    }

    public function getCreatedAtAttribute()
    {
    	return date('d M Y H:i:s A', strtotime($this->attributes['created_at']));
    }

    public function getTimeAttribute()
    {
        return $this->attributes['created_at'];
    }
}
