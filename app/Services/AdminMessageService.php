<?php

namespace App\Services;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Chat;
use DataTables;
use DB;

class AdminMessageService
{
    public function index($request)
    {
        $data = Chat::orderby('created_at', 'DESC')->get()->unique('to_id')->unique('from_id');
        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('from', function($row){
                return '<img src="'.$row->from_image.'" width"50" height="50" /> <a href="'.route('users.show', $row->from_id).'">'.$row->from.'</a>';                
            })
            ->editColumn('to', function($row){
                return '<img src="'.$row->to_image.'" width"50" height="50" /> <a href="'.route('users.show', $row->to_id).'">'.$row->to.'</a>';
            })
            ->editColumn('message', function($row){
                return $row->message;                
            })           
            ->editColumn('date', function($row){
                return date('d F Y', strtotime($row->created_at));                
            })
            ->addColumn('action', function($row){
                return '<a href="'.route('messages.show', [$row->to_id, $row->from_id]).'" class="btn btn-sm bg-success-light"><i class="fas fa-comment"></i> Chat</a>';                
            })
            ->rawColumns(['from', 'to', 'action'])
            ->make(true);
    }   
}
