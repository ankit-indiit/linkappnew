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
use App\ContactUs;
use DataTables;

class AdminContactUsController extends Controller
{
	public function index(Request $request)
	{
		if ($request->ajax()) {
			$data = ContactUs::get();
	        return Datatables::of($data)
	            ->addIndexColumn()
	            ->editColumn('name', function($row){
	                return $row->name;                
	            })
	            ->editColumn('email', function($row){
	                return $row->email;                
	            })
	            ->editColumn('message', function($row){
	                return $row->message;                
	            })            
	            ->addColumn('action', function($row){
	                return '<a href="javascript:void(0)" class="btn btn-sm bg-danger-light" id="deleteContactUs" data-id="'.$row->id.'"><i class="fas fa-trash-alt"></i> Delete</a></td>';                
	            })
	            ->rawColumns(['action'])
	            ->make(true);
		}
		return view('admin.module.contact-us.index');
	}	

	public function destroy($id)
	{
		if (ContactUs::where('id', $id)->delete()) {
			return response()->json([
                'status' => true,
                'message' => 'Contact has been successfully deleted!',
            ]);
		}
	}
}