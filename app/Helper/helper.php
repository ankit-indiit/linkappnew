<?php

use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\Common;
use App\User;
use App\MyInterest;

function getUserNameById($id)
{
	return User::where('id', $id)->pluck('name')->first();
}

function getUserImageById($id)
{
    return User::where('id', $id)->pluck('image')->first();
}

function getUserMatch($authId, $userId)
{
	@$authInterest = MyInterest::where('user_id', $authId)->pluck('interest_id')->toArray();
	@$userInterest = MyInterest::where('user_id', $userId)->pluck('interest_id')->toArray();
	@$similarInterest = array_intersect($authInterest, $userInterest);
	if (count($similarInterest) > 0) {
    	$perecentage = @count($similarInterest) / @count($authInterest) * 100;
	} else {
		$perecentage = 0;
	}
    return round($perecentage);
}

function getNotifications()
{	
    return DB::table('notifications')->orderby('id', 'DESC')->get();
}
function getPackegById($id)
{	
    return DB::table('subscriptions')->where('id', $id)->pluck('title')->first();
}