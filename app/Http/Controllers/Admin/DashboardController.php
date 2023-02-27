<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\User;
use App\Chat;
use App\Like;
use App\Interest;
use App\MyInterest;
use App\Transaction;
use DB;

class DashboardController extends Controller
{
	public function index(Request $request)
	{
		$totalUsers = User::count();
		$totalPayments = DB::table('payments')->sum('amount');
		$chatCount = Chat::orderby('created_at', 'DESC')->get()->unique('to_id')->unique('from_id')->count();
		$totalMatch = DB::table('likes')->sum('match');
		$recentUsers = User::orderby('id', 'DESC')->take(5)->get();
		$recentTransactions = Transaction::orderby('id', 'DESC')->take(5)->get();
		// $userIds = User::pluck('id')->toArray();
	    // $matchedAct = MyInterest::select(DB::raw('count(*) as common_interests, user_id'))
	    //     ->whereIn('user_id', $userIds)
	    //     ->groupBy('user_id')
	    //     // ->havingRaw('COUNT(*) > 1')
	    //     ->get();	    
		return view('admin.module.dashboard.index', compact('totalUsers', 'totalPayments', 'chatCount', 'totalMatch', 'recentUsers', 'recentTransactions'));
	}

	public function updateInterest()
	{
		$users = User::select('id', 'my_interest')->get();
		foreach ($users as $interest) {
			$intsReplace = str_replace(array( '[', ']' ), '', $interest->my_interest);
			$intsArr = explode(',', $intsReplace);
			foreach ($intsArr as $ints) {
				if (Interest::where('id', $ints)->exists() && !MyInterest::where('user_id', $interest->id)->where('interest_id', $ints)->exists()) {
					MyInterest::create(['user_id' => $interest->id, 'interest_id' => $ints]);
				}
			}
		}
		return redirect()->route('dashboard');
	}
}