<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Services\AdminUserService;
use App\User;

class AdminUsersController extends Controller
{
	public function index(Request $request, AdminUserService $adminUserService)
	{
		if ($request->ajax()) {
			return $adminUserService->index($request);
		}
		return view('admin.module.user.index');
	}

	public function create(Request $request)
	{
		return view('admin.module.user.create');
	}

	public function store(Request $request, AdminUserService $adminUserService)
	{
		return $adminUserService->store($request);
	}

	public function edit(Request $request, $id)
	{
		$user = User::findOrFail($id);
		return view('admin.module.user.edit', compact('user'));
	}

	public function show(Request $request, $id, AdminUserService $adminUserService)
	{
		$user = User::findOrFail($id);
		if ($request->ajax()) {
			return $adminUserService->show($id);
		}
		return view('admin.module.user.show', compact('user'));
	}

	public function update(Request $request, $id, AdminUserService $adminUserService)
	{
		return $adminUserService->update($request, $id);
	}

	public function destroy($id, AdminUserService $adminUserService)
	{
		return $adminUserService->destroy($id);
	}
}