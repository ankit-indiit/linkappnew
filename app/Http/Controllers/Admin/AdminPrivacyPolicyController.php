<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Services\AdminPrivacyPolicyService;
use App\User;
use App\PrivacyPolicy;

class AdminPrivacyPolicyController extends Controller
{
	public function index(Request $request, AdminPrivacyPolicyService $privacyPolicy)
	{
		if ($request->ajax()) {
			return $privacyPolicy->index($request);
		}
		return view('admin.module.privacy-policy.index');
	}

	public function create(Request $request)
	{
		return view('admin.module.privacy-policy.create');
	}

	public function store(Request $request, AdminPrivacyPolicyService $privacyPolicy)
	{
		return $privacyPolicy->store($request);
	}

	public function edit(Request $request, $id)
	{
		$privacyPolicy = PrivacyPolicy::findOrFail($id);
		return view('admin.module.privacy-policy.edit', compact('privacyPolicy'));
	}

	public function update(Request $request, $id, AdminPrivacyPolicyService $privacyPolicy)
	{
		return $privacyPolicy->update($request, $id);
	}

	public function destroy($id, AdminPrivacyPolicyService $privacyPolicy)
	{
		return $privacyPolicy->destroy($id);
	}
}