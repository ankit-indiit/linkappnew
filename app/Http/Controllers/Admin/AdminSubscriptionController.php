<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Services\AdminSubscriptionService;
use App\User;
use App\Subscription;

class AdminSubscriptionController extends Controller
{
	public function index(Request $request, AdminSubscriptionService $subscription)
	{
		if ($request->ajax()) {
			return $subscription->index($request);
		}
		return view('admin.module.subscription.index');
	}

	public function create(Request $request)
	{
		return view('admin.module.subscription.create');
	}

	public function store(Request $request, AdminSubscriptionService $subscription)
	{
		return $subscription->store($request);
	}

	public function edit(Request $request, $id)
	{
		$subscription = Subscription::findOrFail($id);
		return view('admin.module.subscription.edit', compact('subscription'));
	}

	public function update(Request $request, $id, AdminSubscriptionService $subscription)
	{
		return $subscription->update($request, $id);
	}

	public function destroy($id, AdminSubscriptionService $subscription)
	{
		return $subscription->destroy($id);
	}
}