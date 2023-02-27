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
use App\Transaction;
use DataTables;

class AdminTransactionController extends Controller
{
	public function index(Request $request, AdminTermsService $adminTermsService)
	{
		if ($request->ajax()) {
			$data = Transaction::select('user_id', 'payment_type', 'package_id', 'subscription_id', 'created_at', 'amount')->orderby('created_at', 'DESC')->get();
	        return Datatables::of($data)
	            ->addIndexColumn()
	            ->editColumn('user_id', function($row){
	                return getUserNameById($row->user_id);                
	            })
	            ->editColumn('package_id', function($row){
	                return 'Basic';                
	            })
	            ->editColumn('subscription_id', function($row){
	                return $row->subscription_id;                
	            })	            
	            ->editColumn('payment_type', function($row){
	                return $row->payment_type;                
	            })
	            ->editColumn('created_at', function($row){
	                return date('d F Y', strtotime($row->created_at));                
	            })
	            ->editColumn('amount', function($row){
	                return '$'.$row->amount;                
	            })
	            ->rawColumns(['action'])
	            ->make(true);
		}
		return view('admin.module.transaction.index');
	}	
}