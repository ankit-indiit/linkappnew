<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class Common extends Authenticatable
{
    public static function insertdata($table,$data){
		DB::table($table)->insert($data);
		return DB::getPdo()->lastInsertId();
	}
	
	public static function getstatuscount($email){
        return DB::table('users')
            ->select('*')
            ->where('email','=',$email)
            ->count();
            
	}
	
		public static function getallcardhomedata($id){
	    $result = DB::table('users')
            ->select('*')
            ->where('status','=','1')
           ->where('id','!=',$id)
           ->orderby('id' , 'desc');
         
           return $result->get();
	}

	public static function updatedata($table,$data,$where){
		return DB::table($table)->where($where)->update($data);
	}

// 	public static function selectdata($table, $where="", $whereIn="", $offset="", $limit=""){
// 		if($offset!=""){

// 			return DB::table($table)->select('*')->where($where)->skip($offset)->get();

// 		}else if($whereIn!=""){

// 			return DB::table($table)->select('*')->where($where)->whereIn($whereIn)->get();
// 		}else{
// 			return DB::table($table)->select('*')->where($where)->get();
// 		}
// 	}

	public static function selectdata($table,$where="",$order="",$offset="",$limit=""){
		if($where!="" && $order=="")
		$result = DB::table($table)->select('*')->where($where);
		else if(!empty($order) && $where!=""){
			if(!empty($order)){
				if (is_array($order)) {
				    foreach ($order as $key => $value) {
				        $order = $order[$key]; // or $v
				        break;
				    }
				}
				}
		$result = DB::table($table)->select('*')->where($where)->orderby($key,$value);
		}
		else
		$result = DB::table($table)->select('*');

		if(is_numeric($offset)){
			$offset = $offset * $limit;
			$result = $result->skip($offset)->take($limit);
		}
		return $result->get();
	}

	public static function deletedata($table,$data){
		return DB::table($table)->where($data)->delete();
	}

	public static function getfirst($table,$where=""){
		if($where!="")
		return DB::table($table)->select('*')->where($where)->first();
		else
		return DB::table($table)->select('*')->first();
	}

	public static function getcount($table,$where=""){
		if($where!="")
		return DB::table($table)->select('*')->where($where)->count();
		else
		return DB::table($table)->select('*')->count();
	}
	
	public static function getfilterdata($gender,$minage,$maxage,$latitude,$longitude,$distance,$id,$type){
	     
	   // $cname = implode(',' ,$cats);
       $result = DB::table('users')
            ->selectRaw("*,
                         ( 6371 * acos( cos( radians(?) ) *
                           cos( radians( current_lat ) )
                           * cos( radians( current_lng ) - radians(?)
                           ) + sin( radians(?) ) *
                           sin( radians( current_lat ) ) )
                         ) AS distance", [$latitude, $longitude, $latitude])
            ->where('status','=','1')
            ->where('id','!=',$id)
            ->where('user_type','=',$type)
           
            // ->orWhereRaw('FIND_IN_SET("'.$cats.'",category)')
            
            ->having("distance", "<", $distance)
             ->orderby('id' , 'desc');
         
			
				$result = 
				$result->where('age','>=',$minage)
				->where('age','<=',$maxage)
				->where('gender','=',$gender);
	             
		
            //   	if($gender == 'Both'){
            //   	 //   echo "if";
            // 	   $result = $result->where('gender','=','Woman')
            // 	   ->orwhere('gender','=','Man');
        	   //  }
        	   //  else{
        	   //        //echo "else";
        	   //       $result = $result->where('gender','=',$gender);
        	   //  }
        	   //  dd($result);
		     
           return $result->get();
            
	}
		
		
		public static function getloggedin($email){
	  
       $result = DB::table('users')
            ->select('*')
            ->where('status','=','1')
            ->where('email','=',$email)
            ->where('social_id','=','');
         
           return $result->get();
            
	}
	
	public static function get_chatusers($id){
	  
       return DB::table('chats')
            ->select('*')
            ->where('to_id','=',$id)
            ->orwhere('from_id','=',$id) 
            ->orderby('id' , 'desc')
            ->get();
	}
	
		public static function get_chats($to_id,$from_id,$pageno){
		    $offset = $pageno * 10;
		return DB::table('chats')
             ->orWhere(function($query) use ($to_id,$from_id){
                 $query->where('from_id', $from_id);
                 $query->where('to_id', $to_id);
             })
             ->orWhere(function($query) use ($to_id,$from_id){
                 $query->where('to_id', $from_id);
                 $query->where('from_id', $to_id);
             })
             ->orderby('created_at' , 'desc')
             ->skip($offset)
            ->take(10)
             ->get();
	}
	
	public static function get_chatsall($to_id,$from_id){
		return DB::table('chats')
             ->orWhere(function($query) use ($to_id,$from_id){
                 $query->where('from_id', $from_id);
                 $query->where('to_id', $to_id);
             })
             ->orWhere(function($query) use ($to_id,$from_id){
                 $query->where('to_id', $from_id);
                 $query->where('from_id', $to_id);
             })
             ->orderby('created_at' , 'desc')
             ->get();
	}
	
	public static function delete_matchuserchat($to_id,$from_id){
		return DB::table('chats')
             ->orWhere(function($query) use ($to_id,$from_id){
                 $query->where('from_id', $from_id);
                 $query->where('to_id', $to_id);
             })
             ->orWhere(function($query) use ($to_id,$from_id){
                 $query->where('to_id', $from_id);
                 $query->where('from_id', $to_id);
             })
             ->delete();
	}
	
	public static function moreloadmovies($minrate,$maxrate,$cats){
	  $result = DB::table('movies')
            ->select('*')
            ->where('status','=','1')
            
             ->orderby('id' , 'desc');
           
			
				$result = $result->where('rating','>=',$minrate)
				->where('rating','<=',$maxrate);
	             
		
          if(!empty($cats)){
			
				$result = $result->whereIn('category',$cats);
	             
		}
           return $result->get();
	}
	
	public static function getreportdata(){
	  $result = DB::table('reports')
            ->select('reports.*','trips.id as tripid','trips.city','trips.type','trips.status as trip_status','users.email as from_email')
            ->join('trips', 'trips.id', '=', 'reports.post_id','left')
            ->join('users', 'users.id', '=', 'reports.from_id','left')
            ->orderby('reports.id','desc');
            
           return $result->get();
	}
	
	public static function gethomedata($id,$pageno){
	    
	    $offset = $pageno * 8;
	  $result = DB::table('trips')
            ->select('trips.*','users.id as userID','users.name as user_name','users.image as user_image','users.email as user_email','users.view_post','users.view_trip','users.message_me','users.comment_me')
            ->join('users', 'users.id', '=', 'trips.userid','left')
            ->where('trips.userid','!=',$id)
            ->where('trips.status','=','1')
            ->orderby('trips.id','desc')
            ->skip($offset)
            ->take(8);
            
           return $result->get();
	}
	
	public static function gethomedatacount($id){
	  $result = DB::table('trips')
            ->select('trips.*','users.id as userID','users.name as user_name','users.image as user_image','users.email as user_email','users.view_post','users.view_trip','users.message_me','users.comment_me')
            ->join('users', 'users.id', '=', 'trips.userid','left')
            ->where('trips.userid','!=',$id)
            ->where('trips.status','=','1')
            ->orderby('trips.id','desc');
            
           return $result->get();
	}
	
	public static function getguideadmindata(){
	  $result = DB::table('trips')
            ->select('trips.*','users.id as userID','users.name as user_name','users.image as user_image','users.email as user_email')
            ->join('users', 'users.id', '=', 'trips.userid','left')
            ->where('trips.type','=','guide')
            ->where('trips.status','=','1')
            ->orderby('trips.id','desc');
            
           return $result->get();
	}
	
	public static function gettripadmindata(){
	  $result = DB::table('trips')
            ->select('trips.*','users.id as userID','users.name as user_name','users.image as user_image','users.email as user_email')
            ->join('users', 'users.id', '=', 'trips.userid','left')
            ->where('trips.type','=','trip')
            ->where('trips.status','=','1')
            ->orderby('trips.id','desc');
            
           return $result->get();
	}
	
	public static function gettripuserdata($id,$type){
	  $result = DB::table('trips')
            ->select('trips.*','users.id as userID','users.name as user_name','users.image as user_image','users.email as user_email')
            ->join('users', 'users.id', '=', 'trips.userid','left')
            ->where('trips.id','=' , $id)
            ->where('trips.type','=' , $type);
            
           return $result->first();
	}
	
	
	public static function getlikesuserdata($id){
	  $result = DB::table('likes')
            ->select('likes.*','users.id as userID','users.name as user_name','users.image as user_image','users.email as user_email')
            ->join('users', 'users.id', '=', 'likes.user_id','left')
            ->where('likes.post_id','=' , $id);
            
           return $result->get();
	}
	
	
	public static function getcommentuserdata($id){
	  $result = DB::table('comments')
            ->select('comments.*','users.id as userID','users.name as user_name','users.image as user_image','users.email as user_email')
            ->join('users', 'users.id', '=', 'comments.user_id','left')
            ->where('comments.post_id','=' , $id);
            
           return $result->get();
	}
	
		public static function relatedmovies($id,$title){
		    
             $myArray = explode(' ', $title);
            //  print_r($myArray);
            //  die;
	  $result = DB::table('movies')
            ->select('*')
            ->where('status','=','1')
            ->where('id','!=',$id);
            $result = $result->where(function($query) use ($myArray){
					foreach ($myArray as $titl) {
	                 $query->orwhere('title', 'like', '%' . $titl . '%');
	             	}
	             });
        //   ->where('title', 'like', '%' . $title . '%');
           
           return $result->get();
	}


}
