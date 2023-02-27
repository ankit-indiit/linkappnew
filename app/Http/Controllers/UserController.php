<?php

namespace App\Http\Controllers;
namespace App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Common;

class UserController extends Controller
{
    
    
     public function register_user(Request $request){
        $common_model = new Common();
          $check = $common_model->getstatuscount($request->email);
            if($check == 0){
           $user_data = array(
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' =>Hash::make($request->password),
                    
                    
                );
                
        $insert = $common_model->insertdata('users',$user_data);
        if($insert){
                    $data['status'] = 1;
                    $data['id'] = $insert;
                    $data['name'] = $request->name;
                    $data['email'] = $request->email;
          
        }
             else{
           $data['status'] = 0;
        }
            }
            else{
                $data['status'] = 2;
            }
        return $data;
    }
    
    public function get_userdetail(Request $request){
        $common_model = new Common();
        $check_token = $common_model->getfirst('users',array('id' => 1));
       
        if(!empty($check_token)){
            $data['result'] = $check_token;
        }
        else{
           $data['status'] = 0;
        }
        return json_encode($data);
    }
    
    
      public function get_chat_users(Request $request){
        $common_model = new Common();
        $chatusers = $common_model->get_chatusers($request->user_id);
        
          $all_from_users = [];
         $alldatass =[];
        foreach($chatusers as $key =>$user){
            // echo 'chatuser1111';
            // print_r($user);
             $from_user = $user[0]['from_id'];
                if($from_user == $request->user_id){
                    $from_user = $user[0]['to_id'];
                }
                
                //  echo 'fromid'.$from_user;
           $user_data = $common_model->getfirst('users',[['id','=', $from_user]]);
           
              if(!empty($user_data) && !in_array($from_user, $all_from_users)){
                    $user[0]['sender_id'] = $user_data->id;
                    $user[0]['sender_name'] = $user_data->name;
                     $user[0]['sender_email'] = $user_data->email;
                    $user[0]['sender_image'] = $user_data->image;
                    array_push($all_from_users, $from_user);
                    array_push($alldatass, $user[0]);
                }
                // print_r($all_from_users);
                
       }
        
        if(!empty($alldatass)){
            $data['result'] = $alldatass;
            $data['status'] = 1;
        }
        else{
           $data['status'] = 0;
        }
        return json_encode($data);
    }
    
    
}
