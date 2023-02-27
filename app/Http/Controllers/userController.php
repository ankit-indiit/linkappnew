<?php

namespace App\Http\Controllers;
use FFMpeg\Filters\Video\VideoFilters;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\User;
use App\Common;


class usercontroller extends Controller
{
    
    
     public function gethome( )
        {
        return view('gethome');
        }
    
    
    /**
    * This function is used for uploading reels, media is uploaded to S3 Bucket.
    * 
    * uploadVideo(Request $request)
    */
 
    
        public function uploadVideo(Request $request)
    {
        
      
        $ffmpeg = FFMpeg\FFMpeg::create();
 
	
        $upload = $ffmpeg->open('uploads/sample.mp4')
            ->export()
            ->resize(640, 480);
            
            print_r($upload);
        
        
        
        // $file = Request::file('file');
        // $filename = $file->getClientOriginalName();
        // $path = public_path().'/uploads/';
        // $file->move($path, $filename);

        // // $path = Storage::disk('s3')->put('reels/videos', $request->file);
        
        
        // $parse = json_decode($path);
        
        //         $newData = array(
               
        //             'name' => $request->get('fileName'),
        //             'userId' => $request->get('userId'),
        //             'type' => 'video',
        //             'caption' => $request->get('caption'),
        //             'tags' => implode(",", json_decode($request->get('tags'))),
        //             'category' => $request->get('category'),
        //             'url' => $path
                    
        //     );

        //     $common_model = new Common();
        //     $insert = $common_model->insertdata('posts',$newData);
        
          
        // return json_encode($path);
        
    }
    
 
    
    /**
    * This function is used for displaying reels on home page on user App, all details related to reels will be fetched.
    * 
    * getReels(Request $request)
    */
 
            public function getReels(Request $request)
    {
            $common_model = new Common();
            $reelsData = $common_model->selectdata('posts', array());
            
            if(count($reelsData)!=0){
                  $list = [];
                  $count = 0;
                  foreach($reelsData as $key){
                      
                        $userData = $common_model->selectdata('users', array('id' => $key->userId));
                        
                        //let's fetch like details of reel
                        $isLiked = $common_model->selectdata('likedReels', array('reelid' => $key->id, 'userId' => $key->userId));
                        $totalLikes = $common_model->getcount('likedReels', array('reelid' => $key->id));
                        
                        //let's fetch comment details of reel
                        $totalComments = $common_model->getcount('reelComments', array('reelid' => $key->id));
                        
                        $dict = [
                                    'caption' => $key->caption,
                                    'category'=> $key->category,
                                    'created_at' => $key->created_at,
                                    'deleted_at' => $key->deleted_at,
                                    'id' => $key->id,
                                    'name' => $key->name,
                                    'tags' => explode(',',$key->tags),
                                    'type' => $key->type,
                                    'updated_at' => $key->updated_at,
                                    'url' => $key->url,
                                    'userId' => $key->userId,
                                    'user' => $userData[0],
                                    'isLiked' => count($isLiked)!=0 ? 1 :0,
                                    'totalLikes' => $totalLikes,
                                    'totalComments' => $totalComments
                                   
                            ];
                        
                        array_push($list, $dict);
                        $count++;
                        
                        if(count($reelsData) == $count){
                                    $data['status'] = 1;
                                    $data['data'] = $list;
                           }
                      
                  }
               

            }
            else{
              
                $data['status'] = 0;
                $data['data'] = $userData;
                
            }
            
           return json_encode($data);
        
    }
    
    
    
        
    /**
    * This function is used for adding comment to reel on user App.
    * 
    * addCommentToReel(Request $request)
    */
 
            public function addCommentToReel(Request $request)
    {
           
        
                $newData = array(
               
                    'reelId' => $request->reelId,
                    'userId' => $request->userId,
                    'comment' => $request->newComment,

            );

            $common_model = new Common();
            $insert = $common_model->insertdata('reelComments', $newData);
            
            
            if($insert){
                
                        $data['status'] = 1;
            }
            
            return json_encode($data);
        
    }
    
    
        /**
    * This function is used fetch all comments of the reel.
    * 
    * fetchAllComments(Request $request)
    */
 
            public function fetchAllComments(Request $request)
    {
           
            $common_model = new Common();
            $list1 = $common_model->selectdata('reelComments', array('reelId' => $request->reelId));
            
            if(count($list1)!=0){
                $list = [];
                $count = 0;
                
                foreach($list1 as $key){
                    
                    $userData = $common_model->selectdata('users', array('id' => $key->userId));
                    
                    $dict = [
                        
                        'name' => $userData[0]->name,
                        'picture' => $userData[0]->picture,
                        'comment' => $key->comment,
                        'time' => $key->created_at
                        ];
                    
                        array_push($list, $dict);
                        $count++;
                        
                        if(count($list) == $count){
                                    $data['status'] = 1;
                                    $data['data'] = $list;
                           }
                }
                
          
            }else{
                
                        $data['status'] = 0;
            }
            
            return json_encode($data);
        
    }
    
    
    /**
    * This function is used for adding or removing reels from the liked ones.
    * 
    * toggleReelToLiked(Request $request)
    */
 
            public function toggleReelToLiked(Request $request)
    {
            $common_model = new Common();
             
             if($request->status == '1'){
                    $newData = array(
                    'reelId' => $request->reelId,
                    'userId' => $request->userId,
             );
            
            $insert = $common_model->insertdata('likedReels', $newData);
            if($insert){
                
                        $data['status'] = 1;
                }else{
                    $data['status'] = 0;
                }
            
             }else{
                 
                 $insert = $common_model->deletedata('likedReels', array('reelId' => $request->reelId, 'userId' => $request->userId));  
                if($insert){
                
                        $data['status'] = 1;
                }else{
                    $data['status'] = 0;
                }
             }

            return json_encode($data);
        
    }
    
    
    
    /**
    * This function is used to let the user authenticate on APPLICATION.
    * 
    * authenticateUser(Request $request)
    */
    
    
       public function authenticateUser(Request $request)
    {

        $userData = User::where('phone','=',$request['phone'])->first();
            
        if($userData != null)
        {
    
            if(Hash::check($request['password'], $userData['password'])){
    
                $data['msg']='You are logged in';
                $data['status'] = 1;
                $data['data'] = $userData;
    
            }else{
    
                $data['msg']='Incorrect password';
                $data['status'] = 2;
                $data['data'] = 'null';
    
            }
           
        }
        else{
                $data['msg']='Account does not exist for this phone';
                $data['status'] = 2;
                $data['data'] = 'null';
        }
        
    		return json_encode($data);
    }
    
    
    
    /**
    * This function is used to add new user on APPLICATION.
    * 
    * registerNewUser(Request $request)
    */



    public function registerNewUser(Request $request)
    {
        
        $Exists = User::where('username','=',$request['username'])->first();    
         
    if($Exists == null)
    {
        $newData = array(
               
                'username' => $request['username'],
                'password' => Hash::make($request['password']),
                'email' => $request['email'],
                'phone' => $request['phone']
            );

            $common_model = new Common();
            $insert = $common_model->insertdata('users',$newData);

            if($insert){
               $userData = $common_model->selectdata('users', array('id' => $insert));
                $data['status'] = 1;
                $data['user'] = $userData;
            }
            else{
                $data['status'] = 0;
            }
    }
    else{
            $data['msg']='This username is taken, Please enter another';
            $data['status'] = 2;
            $data['data'] = 'null';
    }
    
		return json_encode($data);
    }
    
    
    /**
    * This function is used to add new user on APPLICATION.
    * 
    * registerNewUser(Request $request)
    */

    public function test(Request $request)
    {
        $common_model = new Common();
        $userData = $common_model->selectdata('users', array('id' => '7'));
        echo '<pre>';
        print_r($userData);
    }
    
    
    /**
    * This function is used to update user profile.
    * 
    * updateUserProfileData(Request $request)
    */


    public function updateUserProfileData(Request $request)
    {
        
        
         if ($request->hasFile('file')) {
          
                   $path = Storage::disk('s3')->put('user/profilePicture', $request->file);
                   
                   $newData = array(
                    'name' => $request['name'],
                    'address' => $request['address'],
                    'email' => $request['email'],
                    'phone' => $request['phone'],
                    'picture' => $path
                );
         
         }else{

            $newData = array(
                'name' => $request['name'],
                'address' => $request['address'],
                'email' => $request['email'],
                'phone' => $request['phone']
            );

        }
        

            $common_model = new Common();
            $insert = $common_model->updatedata('users', $newData, array('id'=> $request['id']));

            if($insert){
               $userData = $common_model->selectdata('users', array('id' => $request['id']));
                $data['status'] = 1;
                $data['data'] = $userData[0];
            }
            else{
                $data['status'] = 0;
            }
    // }
    // else{
    //         $data['msg']='This username is taken, Please enter another';
    //         $data['status'] = 2;
    //         $data['data'] = 'null';
    // }
    
		return json_encode($data);
    }


    public function updateUserPassword(Request $request)
    {


        $userData = User::where('id','=',$request['id'])->first();
        
        if($userData != null)
        {
    
            if(Hash::check($request['oldPassword'], $userData['password'])){



                $newData = array(
               
                    'password' =>  Hash::make($request['password'])
                  
                );
    
                $common_model = new Common();
                $insert = $common_model->updatedata('users', $newData, array('id'=> $request['id']));
    
                if($insert){
                   $userData = $common_model->selectdata('users', array('id' => $request['id']));
                    $data['status'] = 1;
                    $data['data'] = $userData[0];
                    $data['msg'] = 'Password is changed';
                }
                else{
                    $data['status'] = 0;
                    $data['msg']='Internal server error';
                }

    
            }else{
    
                $data['msg']='Old password is incorrect';
                $data['status'] = 2;
                $data['data'] = 'null';
    
            }
           
        }
        else{
                $data['msg']='Account does not exist for this username';
                $data['status'] = 2;
                $data['data'] = 'null';
        }
        

		return json_encode($data);
    }
    
 
    
    

    public function addProduct(Request $request)
    {
        
 
        $newData = array(
               
                'name' => 'Mobiles' ,
          
            );

            $common_model = new Common();
            $insert = $common_model->insertdata('categories',$newData);

            if($insert){
               $userData = $common_model->selectdata('categories', array('id' => $insert));
                $data['status'] = 1;
                $data['user'] = $userData;
            }
            else{
                $data['status'] = 0;
            }
 
    
		return json_encode($data);
    }

    public function getProducts(Request $request)
    {
        
            $common_model = new Common();
       
            $userData = $common_model->selectdata('products', array());
            if($userData){
              
                $data['status'] = 1;
                $data['user'] = $userData;
            }
            else{
                $data['status'] = 0;
            }
 
    
		return json_encode($data);
    }

    public function addToCart(Request $request)
    {
        $common_model = new Common();
        $exists =  $common_model->selectdata('cart', array('userId' => $request['userId'], 'productId' => $request['productId']));

        if($request['status'] ==1){



            $newData = array(
               
                'userId' => $request['userId'],
                'productId' => $request['productId'],
                // 'pieces' => $request['pieces'],
            );

            if(count($exists)==0){
                $newData['pieces'] = 1;
            }else{
                $newData['pieces'] = (int)($exists[0]->pieces) + 1;
            }
    
          
            $insert = $common_model->insertdata('cart',$newData);
    
            if($insert){
               $userData = $common_model->selectdata('cart', array('id' => $insert));
                $data['status'] = 1;
                $data['user'] = $userData;
            }
            else{
                $data['status'] = 0;
            }

        }else{

 
            $common_model = new Common();
            $userData = $common_model->deletedata('cart', array('userId' => $request['userId'], 'productId' => $request['productId']));
    
            if($userData){
              
                $data['status'] = 1;
                $data['user'] = $userData;
            }
            else{
                $data['status'] = 0;
            }

        }

    return json_encode($data);
    }


    public function manageCartProduct(Request $request)
    {
        $common_model = new Common();

        $exists =  $common_model->selectdata('cart', array('userId' => $request['userId'], 'productId' => $request['productId']));

        if($request['status'] ==1){

            $newData = array(
               
                'userId' => $request['userId'],
                'productId' => $request['productId'],
                // 'pieces' => $request['pieces'],
            );

            if(count($exists)==0){
                $newData['pieces'] = 1;
                $insert = $common_model->insertdata('cart', $newData);
            }else{
                $newData['pieces'] = (int)($exists[0]->pieces) + 1;
                $insert = $common_model->updatedata('cart', ['pieces'=> $newData['pieces'] ], array('userId' => $request['userId'], 'productId' => $request['productId']));
            }
    
           
    
            if($insert){
               $userData = $common_model->selectdata('cart', array('id' => $insert));
                $data['status'] = 1;
                $data['newAmount'] = $newData['pieces'];
            }
            else{
                $data['status'] = 0;
            }

        }else{


            if($exists[0]->pieces == 1){

               
                $userData = $common_model->deletedata('cart', array('userId' => $request['userId'], 'productId' => $request['productId']));
        
                if($userData){
                  
                    $data['status'] = 3;
                    $data['newAmount'] = $userData;
                }
                else{
                    $data['status'] = 0;
                }
                
            }else{
 

               $pieces = $exists[0]->pieces - 1;

            
               $userData = $common_model->updatedata('cart', ['pieces'=> $pieces ], array('userId' => $request['userId'], 'productId' => $request['productId']));

               
                if($userData){
                  
                    $data['status'] = 2;
                    $data['newAmount'] = $pieces;

                }
                else{

                    $data['status'] = 0;

                }

            }

        }

    return json_encode($data);

    }


    public function getCart(Request $request)
    {
        
            $common_model = new Common();
            $userData = $common_model->selectdata('cart', array('userId' => $request['userId']), '');
            if(count($userData)!=0){

                $allIDS = [];
                $allProducts = [];
                $count = 0;
                foreach($userData as $key){

                    $singleProducts = $common_model->selectdata('products', array('id'=> $key->productId));

                 
                    array_push($allIDS, (int)$key->productId );

                    $dict = [
                            'categoryId'=> $singleProducts[0]->categoryId,
                            'created_at'=> $singleProducts[0]->created_at,
                            'deleted_at'=> $singleProducts[0]->deleted_at,
                            'id'=> $singleProducts[0]->id,
                            'name'=> $singleProducts[0]->name,
                            'price'=> $singleProducts[0]->price,
                            'sellerId'=> $singleProducts[0]->sellerId,
                            'updated_at'=> $singleProducts[0]->updated_at,
                            // 'status' => $singleProducts[0]->status,
                            'amount' => $key->pieces
                    ];
                    
                    array_push($allProducts, $dict);
                    $count++;
                    if($count == count($userData)){
                        $data['status'] = 1;
                        $data['user'] = $allIDS;
                        $data['products'] = $allProducts ;
                    }
                
                }
              

            }
            else{
                $data['status'] = 0;
            }
 
    
		return json_encode($data);
    }


    public function getCartWithProducts(Request $request)
    {
        
            $common_model = new Common();
            $userData = $common_model->selectdata('cart', array('userId' => $request['userId']));
            if($userData){

                $allIDS = [];
                $count = 0;
                foreach($userData as $key){
                    array_push($allIDS, (int)$key->productId );
                    $count++;
                    if($count == count($userData)){
                        $userData1 = $common_model->selectdata('products', array('userId' => $request['userId']), array('id' => $allIDS));

                        $data['status'] = 1;
                        $data['user'] = $allIDS;
                        $data['products'] = $userData1;

                    }
                
                }
              

            }
            else{
                $data['status'] = 0;
            }
 
    
		return json_encode($data);
    }

 
   public function getFollowers(Request $request){
    $common_model = new Common();
    $userData = $common_model->selectdata('User_Followers', array('user_id' => $request['userId']));
    if(count($userData)!=0){
              
        $data['status'] = 1;
        $data['user'] = $userData;
    }
    else{
        $data['status'] = 0;
    }
    
    return json_encode($data);

    }


    public function getFollowing(Request $request){
        $common_model = new Common();
        $userData = $common_model->selectdata('User_Followers', array('follower_id' => $request['userId']));
        if(count($userData)!=0){
                  
            $data['status'] = 1;
            $data['user'] = $userData;
        }
        else{
            $data['status'] = 0;
        }
        
        return json_encode($data);
    
        }
        
        
 


}