<?php

namespace App\Services;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Subscription;
use DataTables;
use DB;

class AdminNotificationService
{
    public function index($request)
    {
        $data = DB::table('notifications')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('from', function($row){
                return getUserNameById($row->from_id);                
            })
            ->editColumn('to', function($row){
                return getUserNameById($row->to_id);                
            })
            ->editColumn('message', function($row){
                switch ($row->text) {
                    case 'match':
                        $noti = getUserNameById($row->to_id).' has found a new match with '.getUserNameById($row->from_id);
                    break;
                    case 'register':
                        $noti = 'New user '.getUserNameById($row->from_id).' has Registered on the App';
                    break;                    
                    default:
                        $noti = '';
                    break;
                }
                return $noti;             
            })
            ->editColumn('date', function($row){
                return date('d F Y', strtotime($row->created_at));                
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
