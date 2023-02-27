<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'image', 'gender', 'dob', 'paypal_token'
    ];

    protected $appends = ['interests'];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getImageAttribute()
    {
        $name = isset($this->attributes['name']) ? $this->attributes['name'] : 'Not Found';
        if (str_contains($this->attributes['image'], "profilePic") == true || $this->attributes['image'] == '') {
            return asset("https://ui-avatars.com/api/?name=".@$name."");
        } else {
            return asset("public/assets/img/customer/".$this->attributes['image'].""); 
        }
    }

    public function getInterestsAttribute()
    {
        return MyInterest::where('user_id', $this->attributes['id'])->pluck('interest_id')->toArray();
    }
}