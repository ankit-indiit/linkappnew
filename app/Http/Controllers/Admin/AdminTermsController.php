<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Services\AdminTermsService;
use App\User;
use App\Term;

class AdminTermsController extends Controller
{
	public function index(Request $request, AdminTermsService $adminTermsService)
	{
		if ($request->ajax()) {
			return $adminTermsService->index($request);
		}
		return view('admin.module.terms.index');
	}

	public function create(Request $request)
	{
		return view('admin.module.terms.create');
	}

	public function store(Request $request, AdminTermsService $adminTermsService)
	{
		return $adminTermsService->store($request);
	}

	public function edit(Request $request, $id)
	{
		$term = Term::findOrFail($id);
		return view('admin.module.terms.edit', compact('term'));
	}

	public function update(Request $request, $id, AdminTermsService $adminTermsService)
	{
		return $adminTermsService->update($request, $id);
	}

	public function destroy($id, AdminTermsService $adminTermsService)
	{
		return $adminTermsService->destroy($id);
	}
}