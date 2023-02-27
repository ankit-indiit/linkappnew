<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Services\AdminNotificationService;
use App\User;
use App\Subscription;
use DB;

class AdminNotificationController extends Controller
{
	public function index(Request $request, AdminNotificationService $notification)
	{
		// if ($request->ajax()) {
		// 	return $notification->index($request);
		// }
		$notifications = DB::table('notifications')->orderby('created_at', 'DESC')->paginate(10);
		return view('admin.module.notification', compact('notifications'));
	}	
}