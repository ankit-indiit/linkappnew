<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Redirect,Hash,Session,Mail,Exception;
use App\Common;
use Carbon\Carbon;

class Admin extends Controller
{
	public function __construct(){
		$this->middleware(function ($request, $next){
	        $is_logged_in = Session::get('admin_looged_in');
	        if(!isset($is_logged_in) || $is_logged_in != '1'){
				return Redirect::to('admin')->send();
			}
			else{
                 
				return $next($request);
			}
	    });
    }
    
    public function check_admin_token(Request $request){
        $common_model = new Common();
        $check_token = $common_model->getfirst('admin',array('id' => $request->userId));
        if(!empty($check_token)){
            $data['status'] = 1;
        }
        else{
           $data['status'] = 0;
        }
        return json_encode($data);
    }
}