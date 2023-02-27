<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    const UPDATED_AT = null;
    
    protected $fillable = ['from_id', 'to_id', 'match', 'is_read', 'status', 'created_at'];
    
    // public function getCreatedAtAttribute()
    // {
    //     return date('d F Y', strtotime($this->attributes['from_id']));
    // }
}
