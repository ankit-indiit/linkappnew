<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Services\AdminMessageService;
use App\User;
use App\Chat;

class AdminMessagesController extends Controller
{
	public function index(Request $request, AdminMessageService $adminMessage)
	{		
		if ($request->ajax()) {
			return $adminMessage->index($request);
		}
		return view('admin.module.message.index');
	}

	public function show($toId, $fromId)
	{		
		$messages = Chat::where([
                ['to_id', $toId],
                ['from_id', $fromId]
            ])
			->orWhere([
                ['to_id', $fromId],
                ['from_id', $toId]
            ])
            ->orderBy('id', 'ASC')
            ->get();

		return view('admin.module.message.show', compact('messages','toId'));
	}
}