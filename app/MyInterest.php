<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MyInterest extends Model
{
    protected $fillable = ['user_id', 'interest_id'];
}
