<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PrivacyPolicy extends Model
{
    protected $fillable = ['title', 'description'];

    protected $appends = ['short_desc'];

    public function getShortDescAttribute()
    {
        return Str::limit($this->attributes['description'], 100);
    }
}
