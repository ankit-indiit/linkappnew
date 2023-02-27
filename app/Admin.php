<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $guard = 'admin';

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getImageAttribute()
    {
        if ($this->attributes['image'] != '') {
            return asset("public/assets/img/".$this->attributes['image']."");
        } else {
            return asset("https://ui-avatars.com/api/?name=".@$this->attributes['name']."");
        }
    }
}
