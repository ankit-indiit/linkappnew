<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App,
    Redirect,
    Hash,
    Session,
    Mail,
    File,
    DateTime,
    DatePeriod,
    DateInterval,
    Exception;
use App\Common;
use App\User;
use App\MyInterest;
use App\Term;
use App\PrivacyPolicy;
use App\ContactUs;
use App\Like;
use Newsletter;
use QrCode;
use Carbon\Carbon;
use Twilio\Rest\Client;
use Twilio\Jwt\ClientToken;
use DrewM\MailChimp\MailChimp;
use \DOMDocument;
use \DOMXpath;
use Sunra\PhpSimple\HtmlDomParser;
use DB;

class Front extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $current_locale = session("current_locale");
            if (!empty($current_locale)) {
                App::setLocale($current_locale);
            }
            return $next($request);
        });
    }

    public function socialLOGIN(Request $request)
    {
        $common_model = new Common();

        $check = $common_model->getcount("users", [
            "social_id" => $request->social_id,
        ]);
        $check_login = $common_model->getfirst("users", [
            "status" => 1,
            "email" => $request->email,
            "type" => $request->type,
        ]);
        if ($check == 0) {
            $user_data = [
                "name" => $request->name,
                "email" => $request->email,
                "social_id" => $request->social_id,
                "type" => $request->type,
                "image" => $request->image ? $request->image : "",
                "view_post" => "Everyone",
                "view_trip" => "Everyone",
                "message_me" => "Everyone",
                "comment_me" => "Everyone",
            ];
            $insert = $common_model->insertdata("users", $user_data);
            if ($insert) {
                $data["status"] = 1;
                $data["id"] = $insert;
                $data["name"] = $request->name;
                $data["email"] = $request->email;
                $data["social_id"] = $request->social_id;
                $data["image"] = $request->image ? $request->image : "";
                $data["phone"] = "";
            } else {
                $data["status"] = 0;
            }
        } else {
            $dataimage = $request->image ? $request->image : "";
            $update = $common_model->updatedata(
                "users",
                ["image" => $dataimage],
                ["id" => $check_login->id]
            );

            $data["status"] = 1;
            $data["id"] = $check_login->id;
            $data["name"] = $check_login->name;
            $data["email"] = $check_login->email;
            $data["social_id"] = $check_login->social_id;
            $data["image"] = $check_login->image;
            $data["phone"] = $check_login->phone;
        }
        return json_encode($data);
    }

    public function register_user(Request $request)
    {
        $common_model = new Common();
        $file = $request->file("video");
        $vdeo_name = "";
        if (!empty($file)) {
            //Move Uploaded File
            $destinationPath = "images";
            $original_name = $file->getClientOriginalName();
            $file_name = str_replace(
                " ",
                "_",
                time() . $file->getClientOriginalName()
            );
            if ($file->move($destinationPath, $file_name)) {
                $vdeo_name = $file_name;
            }
        }

        $user_data = [
            "name" => $request->name,
            "email" => $request->email,
            "dob" => $request->dob,
            "gender" => $request->gender,
            "is_gender_show" => $request->is_gender_show,
            "sexual_orentation" => $request->sexual_orentation,
            "is_orentation_show" => $request->is_orentation_show,
            "employment_type" => $request->employment_type,
            "employ_designation" => $request->employ_designation,
            "student_university" => $request->student_university,
            "lat" => $request->lat,
            "lng" => $request->lng,
            // "my_interest" => $request->my_interest,
            "bio" => $request->bio,
            "intro_video" => $vdeo_name,
            "user_type" => $request->user_type,
            "password" => Hash::make($request->password),
        ];

        $insert = $common_model->insertdata("users", $user_data);        
        if ($insert) {
            if (!empty($request->images)) {
                $imgs = json_decode($request->images, true);
                foreach ($imgs as $img) {
                    print_r($img);
                    $fname = $img["filename"];
                    $destinationPath = "images";
                    $original_name = $fname->getClientOriginalName();
                    $file_name1 = str_replace(
                        " ",
                        "_",
                        time() . $fname->getClientOriginalName()
                    );
                    if ($file->move($destinationPath, $file_name1)) {
                        $img_name = $file_name1;
                    }
                    $common_model->insertdata("images", [
                        "user_id" => $insert,
                        "images" => $img_name,
                    ]);
                }
            }            
            $data["status"] = 1;
            $data["id"] = $insert;
            $data["name"] = $request->name;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function signup_user(Request $request)
    {
        $common_model = new Common();
        // $check_login = $common_model->getfirst('users',array('email' => $request->email));
        //   if(!empty($check_login)){
        //     $data['status'] = 2;
        //     $data['verify_status'] = $check_login->status;
        //     $data['id']= $check_login->id;
        //   }
        //   else{

        $user_data = [
            "user_type" => $request->user_type,
            "name" => $request->name,
            "email" => $request->email,
            "status" => "2",
            "password" => Hash::make($request->password),
        ];
        // echo 'test';
        // die;
        $insert = $common_model->insertdata("users", $user_data);
        if ($insert) {
            $digits = 4;
            $token = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
            $common_model->updatedata(
                "users",
                ["token" => $token],
                ["id" => $insert]
            );
            $reset_link = $token;
            $email_data = [
                "username" => $request->name,
                "reset_link" => $reset_link,
            ];
            $email_to = $request->email;
            $name_to = $request->name;
            Mail::send("emails.verifyemail", $email_data, function ($message) use ($name_to, $email_to) {
                $message->to($email_to, $name_to)->subject("Verify Email - Link Up");
                $message->from("developerindiit@gmail.com", "Link Up");
            });
            DB::table('notifications')->insert([
                'from_id' => $insert,
                'text' => 'register',
            ]);
            $data["status"] = 1;
            $data["id"] = $insert;
            $data["name"] = $request->name;
        } else {
            $data["status"] = 0;
        }
        //   }

        return json_encode($data);
    }

    public function get_packageplan(Request $request)
    {       
        $common_model = new Common();
        if (isset($request->planId)) {
            $start_date = Carbon::now()->format("Y-m-d");
            $expiredate = Carbon::parse($start_date)
                ->addMonths((int) 1)
                ->format("Y-m-d");
            $user_data = [
                "user_id" => $request->user_id,
                "product_id" => $request->prodId,
                "plan_id" => $request->planId,
                "create_date" => $start_date,
                "expire_date" => $expiredate,
                "pack_amount" => "4.99",
            ];
            User::where('id', $request->user_id)->update(['paypal_token' => $request->paypal_token]);
            $common_model->insertdata("packages", $user_data);
            $data["status"] = 1;
            $data["result"] = @$request->all();
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
        // $ch = curl_init();
        // $clientID = 'AXqBCpO3pdv5RdRWM4x6YThGRJBmNQjE1rxsLwsAHA6J1C2vMYgU4_DfH3wYOhSEGInk1nPg6bemrCKb';
        // $secretID = 'EIf5kB4fP0Q25tEKv64UKVc-sZKimHP557omHkYZBJq1nrNprUEOxttLuSZuQbzeesmPpT5iCbP5aBUr';

        // curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
        // curl_setopt($ch, CURLOPT_HEADER, false);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        // curl_setopt($ch, CURLOPT_USERPWD, $clientID.":".$secretID);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        // $result = curl_exec($ch);       
        // $json = json_decode($result);
        // $token = $json->access_token;    

        // $common_model = new Common();

        // $curl = curl_init();
        // curl_setopt_array($curl, [
        //     CURLOPT_URL => "https://api.sandbox.paypal.com/v1/catalogs/products",
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 30,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "POST",
        //     CURLOPT_POSTFIELDS => "{\"name\":\"Unlimited Access\",\r\n\"description\":\"Unlimited Access\",\r\n\"type\":\"SERVICE\",\r\n\"category\":\"SOFTWARE\"\r\n}",
        //     CURLOPT_HTTPHEADER => [
        //         "authorization: Basic $token",
        //         // "authorization: Basic QWV4TWlDUWZESHB4SkxvREFVMXNteUZ6bXlvYURJMTI4S0wxTWJkQnNpbnVGcTlvSTMtS3Z6MWl4TldzVFBkaDE5SWRYN2JWM3p3UnpKdGg6RVBwQkFadGF4ZXA0SlE5WGNtYjBaT05rd0dKamVDb0JXdUN0cjYwNS01ZjFia0JhZEwwZENNMmhqQm5kOXlNSUs0b0VPdWt1ZmVJSU5XSHc=",
        //         "cache-control: no-cache",
        //         "content-type: application/json",
        //         "postman-token: a6352bbe-6124-7d5d-b969-5067a198eb01",
        //         "token: Bearer",
        //     ],
        // ]);

        // $response = curl_exec($curl);
        // $err = curl_error($curl);
        // curl_close($curl);        

        // if ($err) {
        //     echo "cURL Error #:" . $err;
        // } else {
        //     $cdata = json_decode($response, true);
        //     echo '<pre>';
        //     print_r($cdata);
        //     die;
        //     $prodid = $cdata["id"];
        // }

        // if ($response) {
        //     $curl = curl_init();
        //     curl_setopt_array($curl, [
        //         CURLOPT_URL => "https://api.sandbox.paypal.com/v1/billing/plans",
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => "",
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 30,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => "POST",
        //         CURLOPT_POSTFIELDS => "{\r\n\"product_id\": \"$prodid\",\r\n\"name\": \"Basic Plan\",\r\n\"description\": \"Basic plan\",\r\n\"billing_cycles\": [\r\n\r\n{\r\n\"frequency\": {\r\n\"interval_unit\": \"MONTH\",\r\n\"interval_count\": 1\r\n},\r\n\"tenure_type\": \"REGULAR\",\r\n\"sequence\": 1,\r\n\"total_cycles\": 1,\r\n\"pricing_scheme\": {\r\n\"fixed_price\": {\r\n\"value\": \"4.99\",\r\n\"currency_code\": \"USD\"\r\n}\r\n}\r\n}\r\n],\r\n\"payment_preferences\": {\r\n\"service_type\": \"PREPAID\",\r\n\"auto_bill_outstanding\": true,\r\n\"setup_fee\": {\r\n\"value\": \"0.1\",\r\n\"currency_code\": \"USD\"\r\n},\r\n\"setup_fee_failure_action\": \"CONTINUE\",\r\n\"payment_failure_threshold\": 3\r\n},\r\n\"quantity_supported\": true,\r\n\"taxes\": {\r\n\"percentage\": \"0.1\",\r\n\"inclusive\": false\r\n}\r\n}",
        //         CURLOPT_HTTPHEADER => [
        //             "authorization: Basic $token",
        //             // "authorization: Basic QWV4TWlDUWZESHB4SkxvREFVMXNteUZ6bXlvYURJMTI4S0wxTWJkQnNpbnVGcTlvSTMtS3Z6MWl4TldzVFBkaDE5SWRYN2JWM3p3UnpKdGg6RVBwQkFadGF4ZXA0SlE5WGNtYjBaT05rd0dKamVDb0JXdUN0cjYwNS01ZjFia0JhZEwwZENNMmhqQm5kOXlNSUs0b0VPdWt1ZmVJSU5XSHc=",
        //             "cache-control: no-cache",
        //             "content-type: application/json",
        //             "postman-token: 140ae049-5062-2dc4-e538-4761d3ea2365",
        //             "token: Bearer",
        //         ],
        //     ]);

        //     $response1 = curl_exec($curl);
        //     $err = curl_error($curl);

        //     curl_close($curl);

        //     if ($err) {
        //         echo "cURL Error #:" . $err;
        //     } else {
        //         $plandata = json_decode($response1, true);
        //         //   echo gettype($plandata);
        //     }

        //     if (isset($request->planId)) {
        //         $start_date = Carbon::now()->format("Y-m-d");
        //         $expiredate = Carbon::parse($start_date)
        //             ->addMonths((int) 1)
        //             ->format("Y-m-d");
        //         $user_data = [
        //             "user_id" => $request->user_id,
        //             "product_id" => $request->prodId,
        //             "plan_id" => $request->planId,
        //             "create_date" => $start_date,
        //             "expire_date" => $expiredate,
        //             "pack_amount" => "4.99",
        //         ];
        //         $common_model->insertdata("packages", $user_data);
        //         $data["status"] = 1;
        //         $data["result"] = $plandata;
        //     } else {
        //         $data["status"] = 0;
        //     }
        // } else {
        //     $data["status"] = 0;
        // }
        // return json_encode($data);
    }

    public function add_paymentdataa(Request $request)
    {
        $common_model = new Common();
        $start_date = Carbon::now()->format("Y-m-d");
        $expiredate = Carbon::parse($start_date)
            ->addMonths((int) 1)
            ->format("Y-m-d");
        $user_data = [
            "user_id" => $request->user_id,
            "amount" => $request->amount,
            "payment_type" => "paypal",
            "package_id" => $request->package_id,
            "package_title" => "Unlimited Access",
            "package_month" => "1",
            "transaction_id" => "",
            "order_id" => "",
            "subscription_id" => $request->subscription_id,
            "plan_id" => $request->plan_id,
            "product_id" => $request->product_id,
            "start_date" => $start_date,
            "expiry_date" => $expiredate,
            "subscriber_account_email" => $request->subscriber_account_email,
            "subscriber_payer_id" => "",
        ];

        $insert = $common_model->insertdata("payments", $user_data);
        if ($insert) {
            $common_model->updatedata(
                "users",
                ["is_subscribe" => "1"],
                ["id" => $request->user_id]
            );
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function update_userdata(Request $request)
    {
        $common_model = new Common();

        $date = Carbon::now();
        $todayDate = $date->format("Y-m-d");
        $age = "";
        if ($request->dob) {
            $lastdate = Carbon::parse($request->dob);
            $age = $lastdate->diffInYears($todayDate);
        }

        $user_data = [
            "dob" => $request->dob,
            "age" => $age,
            "gender" => $request->gender,
            "is_gender_show" => $request->is_gender_show,
            "sexual_orentation" => $request->sexual_orentation,
            "is_orentation_show" => $request->is_orentation_show,
            "employment_type" => $request->employment_type,
            "employ_designation" => $request->employ_designation,
            "student_university" => $request->student_university,
            "lat" => $request->lat,
            "lng" => $request->lng,
            // "my_interest" => $request->my_interest,
            "bio" => $request->bio,
            // 'username' => $request->username
        ];
        $update = $common_model->updatedata("users", $user_data, [
            "id" => $request->id,
        ]);
        if ($request->my_interest) {
            foreach ($request->my_interest as $interest) {
                MyInterest::create([
                    'user_id' => $request->id,
                    'interest_id' => $interest,
                ]);
            }
        }
        if ($update) {
            $data["status"] = 1;
            $data["id"] = $request->id;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function add_users(Request $request)
    {
        $file = $request->file("file");
        $image_name = "";
        if (!empty($file)) {
            //Move Uploaded File
            $destinationPath = "images";
            $original_name = $file->getClientOriginalName();
            $file_name = str_replace(
                " ",
                "_",
                time() . $file->getClientOriginalName()
            );
            if ($file->move($destinationPath, $file_name)) {
                $image_name = $file_name;
            }
        }

        if (!empty($request->email)) {
            $common_model = new Common();
            $check = $common_model->getstatuscount($request->email);
            if ($check == 0) {
                $user_data = [
                    "name" => $request->name,
                    "phone" => $request->phone,
                    "email" => $request->email,
                    "fav_activity" => $request->favorite_activity,
                    "how_long_away" => $request->how_long_away,
                    "where_go" => $request->where_go,
                    "with_whom" => $request->with_whom,
                    "where_to_stay" => $request->do_stay,
                    "about_us" => $request->about_us,
                    "password" => Hash::make($request->password),
                ];
                if (!empty($image_name)) {
                    $user_data["image"] = $image_name;
                }

                $insert = $common_model->insertdata("users", $user_data);
                if ($insert) {
                    $data["status"] = 1;
                } else {
                    $data["status"] = 0;
                }
            } else {
                $data["status"] = 2;
            }
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function check_email(Request $request)
    {
        $common_model = new Common();
        $check_login = $common_model->getstatuscount($request->email);
        if (!empty($check_login)) {
            $data["status"] = 0;
        } else {
            $data["status"] = 1;
        }

        return json_encode($data);
    }

    public function delete_mapregion(Request $request)
    {
        $common_model = new Common();

        $insert = $common_model->deletedata("map_region", [
            "user_id" => $request->id,
            "region" => $request->region,
        ]);
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function delete_imags(Request $request)
    {
        $common_model = new Common();

        $insert = $common_model->deletedata("images", ["id" => $request->id]);
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function get_mymapregion(Request $request)
    {
        $common_model = new Common();
        $rgdata = $common_model->selectdata("map_region", [
            "user_id" => $request->id,
        ]);

        if (!empty($rgdata)) {
            $data["result"] = $rgdata;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function login_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required",
            "password" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first(),
            ]);
        }

        $user = DB::table("users")
            ->where("email", $request->email)
            ->first();
        if ($user) {
            $credentials = [
                "email" => $request->get("email"),
                "password" => $request->get("password"),
            ];
            if (Hash::check($request->password, $user->password)) {
                if (Auth::attempt($credentials)) {
                    $status = true;
                    $message = "Login successful";
                    $user = $user;
                }
            } else {
                $status = false;
                $message = "Incorrect Password!";
            }
        } else {
            $status = false;
            $message = "Account doesn’t exist";
        }

        return response()->json([
            "status" => @$status,
            "message" => @$message,
            "data" => $status == true ? @$user : "",
        ]);

        // if(!empty($request->email) && !empty($request->password)){
        //     $common_model = new Common();
        //     $check_login = $common_model->getfirst('users',array('email' => $request->email));
        //     if(!empty($check_login)){
        //         if($check_login->status == '1'){
        //              if (Hash::check($request->password, $check_login->password)) {
        //             Auth::logout();
        //             $data['status'] = 1;
        //             $data['id'] = $check_login->id;
        //             $data['name'] = $check_login->name;
        //             $data['dob'] = $check_login->dob;
        //             $data['location'] = $check_login->current_location;
        //             $data['bio'] = $check_login->bio;
        //             $data['image'] = $check_login->image;
        //         }
        //         else{
        //             $data['status'] = 0;
        //         }

        //         }
        //         else{
        //              if (Hash::check($request->password, $check_login->password)) {
        //           $data['status'] = 2;
        //           $data['id'] = $check_login->id;
        //         }
        //          else{
        //       $data['status'] = 0;
        //     }
        //         }

        //     }
        //     else{
        //       $data['status'] = 0;
        //     }
        // }
        // else{
        //     $data['status'] = 0;
        // }
        // return json_encode($data);
    }

    public function update_step1(Request $request)
    {
        $common_model = new Common();
        $user_data = [];
        if ($request->fav_activity) {
            $user_data["fav_activity"] = $request->fav_activity;
        }
        if ($request->where_go) {
            $user_data["where_go"] = $request->where_go;
        }
        if ($request->how_long) {
            $user_data["how_long_away"] = $request->how_long;
        }
        if ($request->with_whom) {
            $user_data["with_whom"] = $request->with_whom;
        }
        if ($request->where_stay) {
            $user_data["where_to_stay"] = $request->where_stay;
        }

        $update = $common_model->updatedata("users", $user_data, [
            "id" => $request->id,
        ]);
        if ($update) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function upload_profileimg(Request $request)
    {
        $common_model = new Common();        
        $image_name = "";
        //   print_r($request->image);
        if ($request->hasFile("image")) {
            // //Move Uploaded File
            // $destinationPath = "images";
            // $original_name = $file->getClientOriginalName();
            // $file_name = str_replace(" ", "_", time() . $file->getClientOriginalName());
            // if ($file->move($destinationPath, $file_name)) {
            //     $image_name = $file_name;
            // }
            $file = $request->file("image");
            $image_name = time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('assets/img/customer');
            $file->move($destinationPath, $image_name);
        }
        $update = $common_model->updatedata("users",
            ["image" => $image_name],
            ["id" => $request->id]
        );
        if ($update) {
            $data["status"] = 1;
            $data["image"] = $image_name;
            $data["id"] = $request->id;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function upload_uservideo(Request $request)
    {
        $common_model = new Common();
        $file = $request->file("video");
        $image_name = "";
        //   print_r($request->image);
        if (!empty($file)) {
            //Move Uploaded File
            $destinationPath = "images";
            $original_name = $file->getClientOriginalName();
            $file_name = str_replace(
                " ",
                "_",
                time() . $file->getClientOriginalName()
            );
            if ($file->move($destinationPath, $file_name)) {
                $image_name = $file_name;
            }
        }
        $update = $common_model->updatedata(
            "users",
            ["intro_video" => $image_name],
            ["id" => $request->id]
        );
        if ($update) {
            $data["status"] = 1;
            $data["video"] = $image_name;
            $data["id"] = $request->id;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function upload_allimg(Request $request)
    {
        $common_model = new Common();        
        $image_name = "";
        if ($request->hasFile("image")) {
            //Move Uploaded File
            // $destinationPath = "images";
            // $original_name = $file->getClientOriginalName();
            // $file_name = str_replace(" ", "_", time() . $file->getClientOriginalName());
            // if ($file->move($destinationPath, $file_name)) {
            //     $image_name = $file_name;
            // }
            $file = $request->file("image");
            $image_name = time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads');
            $file->move($destinationPath, $image_name);
        }
        $update = $common_model->insertdata("images", [
            "user_id" => $request->user_id,
            "images" => $image_name,
        ]);
        if ($update) {
            $data["status"] = 1;
            $data["image"] = $image_name;
            $data["id"] = $update;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function upload_multiimg(Request $request)
    {
        $common_model = new Common();
        // $allarr = json_decode(stripslashes($request->images), true );
        $allarr = json_decode($request->images, true);

        // dd($allarr);
        echo "filearrrr";
        // var_dump($allarr);
        print_r($allarr[0]);
        $update = "";
        foreach ($allarr as $key => $img) {
            echo "filenameee" . $img;
            $file = $request->file($img);
            $image_name = "";
            //   print_r($request->image);
            if (!empty($file)) {
                //Move Uploaded File
                $destinationPath = "images";
                $original_name = $file->getClientOriginalName();
                $file_name = str_replace(
                    " ",
                    "_",
                    time() . $file->getClientOriginalName()
                );
                if ($file->move($destinationPath, $file_name)) {
                    $image_name = $file_name;
                }
                $update = $common_model->insertdata("images", [
                    "user_id" => $request->user_id,
                    "images" => $image_name,
                ]);
            }
        }

        if ($update) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function update_userprofile(Request $request)
    {              
        DB::beginTransaction();
    
        try {

            $date = Carbon::now();
            $todayDate = $date->format("Y-m-d");
            $age = "";
            if ($request->dob) {
                $lastdate = Carbon::parse($request->dob);
                $request['age'] = $lastdate->diffInYears($todayDate);
            }

            DB::table('users')->where('id', $request->id)->update([
                'name' => $request->name,
                'current_location' => $request->location,
                'current_lat' => $request->current_lat,
                'current_lng' => $request->current_lng,
                'bio' => $request->bio,
                'dob' => $request->dob,
                'employment_type' => $request->employment_type,
                'employ_designation' => $request->employ_designation,
                'student_university' => $request->student_university,
                'lat' => $request->lat,
                'lng' => $request->lng,
                'gender' => $request->gender,
                'sexual_orentation' => $request->sexual_orentation,
                'age' => $request->age,
            ]);

            if ($request->my_interest) {
                $myInterest = str_replace(array( '[', ']' ), '', $request->my_interest);
                $myInterestArr = explode(',', $myInterest);
                foreach ($myInterestArr as $interest) {
                    MyInterest::create([
                        'user_id' => $request->id,
                        'interest_id' => $interest,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'User has been successfully updated!',
            ]);

        } catch (\Exception $e) {            
            DB::rollback();
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ]);
        }


        // $common_model = new Common();
        // $date = Carbon::now();
        // $todayDate = $date->format("Y-m-d");
        // $age = "";
        // if ($request->dob) {
        //     $lastdate = Carbon::parse($request->dob);
        //     $age = $lastdate->diffInYears($todayDate);
        // }

        // $user_data = [
        //     "name" => $request->name,
        //     "current_location" => $request->location,
        //     "current_lat" => $request->current_lat,
        //     "current_lng" => $request->current_lng,
        //     "bio" => $request->bio,
        //     "dob" => $request->dob,
        //     "employment_type" => $request->employment_type,
        //     "employ_designation" => $request->employ_designation,
        //     "student_university" => $request->student_university,
        //     "lat" => $request->lat,
        //     "lng" => $request->lng,
        //     "gender" => $request->gender,
        //     "sexual_orentation" => $request->sexual_orentation,
        //     // "my_interest" => $request->my_interest,
        //     // 'username' => $request->username
        // ];
        // if ($age != "") {
        //     $user_data["age"] = $age;
        // }
        // $update = $common_model->updatedata("users", $user_data, [
        //     "id" => $request->id,
        // ]);
        // if ($request->my_interest) {
        //     foreach ($request->my_interest as $interest) {
        //         MyInterest::create([
        //             'user_id' => $request->id,
        //             'interest_id' => $interest,
        //         ]);
        //     }
        // }
        
        // if ($update) {
        //     $data["status"] = 1;
        //     $data["id"] = $request->id;
        // } else {
        //     $data["status"] = 0;
        // }
        // return json_encode($data);
    }

    public function edit_users(Request $request)
    {
        $common_model = new Common();
        $check_login = $common_model->getfirst("users", [
            "status" => 1,
            "email" => $request->email,
            ["id", "!=", $request->id],
        ]);
        if (empty($check_login)) {
            $file = $request->file("file");
            $image_name = "";
            if (!empty($file)) {
                //Move Uploaded File
                $destinationPath = "images";
                $original_name = $file->getClientOriginalName();
                $file_name = str_replace(
                    " ",
                    "_",
                    time() . $file->getClientOriginalName()
                );
                if ($file->move($destinationPath, $file_name)) {
                    $image_name = $file_name;
                }
            }
            $user_data = [
                "name" => $request->name,

                "email" => $request->email,
                "phone" => $request->phone,
                "about_us" => $request->about_us,
                "fav_activity" => $request->favorite_activity,
                "where_go" => $request->where_go,
                "how_long_away" => $request->how_long_away,
                "with_whom" => $request->with_whom,
                "where_to_stay" => $request->do_stay,
                // 'username' => $request->username
            ];
            if (!empty($image_name)) {
                $user_data["image"] = $image_name;
            }

            if (!empty($request->password)) {
                $user_data["password"] = Hash::make($request->password);
            }
            $update = $common_model->updatedata("users", $user_data, [
                "id" => $request->id,
            ]);
            if ($update) {
                $data["status"] = 1;
            } else {
                $data["status"] = 0;
            }
        } else {
            $data["status"] = 2;
        }

        return json_encode($data);
    }

    public function update_tripdesc(Request $request)
    {
        $common_model = new Common();

        $update = $common_model->updatedata(
            "trips",
            ["description" => $request->description],
            ["id" => $request->id]
        );
        if ($update) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function update_aboutguidecity(Request $request)
    {
        $common_model = new Common();

        $update = $common_model->updatedata(
            "trips",
            ["about_city" => $request->about_city],
            ["id" => $request->id]
        );
        if ($update) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function add_placesdata(Request $request)
    {
        $common_model = new Common();

        //   print_r($request['dynamic_dataarray']);
        //   die;

        if (!empty($request["dynamic_dataarray"])) {
            foreach ($request["dynamic_dataarray"] as $place2) {
                $user_data = [
                    "title" => $place2["title"],
                    "location" => $place2["location"],
                    "description" => $place2["description"],
                    "lat" => $place2["lat"],
                    "phone" => $place2["phone"] ? $place2["phone"] : "",
                    "website" => $place2["website"] ? $place2["website"] : "",
                    "basic_info" => $place2["basic_info"]
                        ? $place2["basic_info"]
                        : "",

                    //  'city' => Str::contains($place2['city'], 'To') ? $place2['city'] : 'Trip To '.$place2['city'],
                    "lng" => $place2["lng"],
                ];
                if (strpos($place2["city"], "Trip To") !== false) {
                    $user_data["city"] = $place2["city"];
                } else {
                    $user_data["city"] = "Trip To " . $place2["city"];
                }

                $update = $common_model->updatedata("trip_places", $user_data, [
                    "id" => $place2["id"],
                ]);

                if (!empty($place2["place_id"])) {
                    $curl = curl_init();

                    curl_setopt_array($curl, [
                        CURLOPT_URL =>
                            "https://maps.googleapis.com/maps/api/place/details/json?place_id=" .
                            $place2["place_id"] .
                            "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => [
                            "cache-control: no-cache",
                            "postman-token: c6a8eca4-b200-b0ae-04ce-f1368e1a00d4",
                        ],
                    ]);

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    curl_close($curl);

                    if ($err) {
                        echo "cURL Error #:" . $err;
                    } else {
                        //   echo $response;
                        $placerefer = json_decode($response, true);
                        //   print_r($placerefer['result']['photos'][0]['photo_reference']);
                        //   echo $placerefer['result']['photos'][0]['photo_reference'];

                        //   $place_refer_id = $placerefer['result']['photos'] ? $placerefer['result']['photos'][0]['photo_reference'] : '';
                        if (array_key_exists("photos", $placerefer["result"])) {
                            $place_refer_id =
                                $placerefer["result"]["photos"][0][
                                    "photo_reference"
                                ];
                        } else {
                            $place_refer_id = "";
                        }
                    }

                    if ($place_refer_id != "") {
                        $curl1 = curl_init();

                        curl_setopt_array($curl1, [
                            CURLOPT_URL =>
                                "https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photo_reference=" .
                                $place_refer_id .
                                "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "GET",
                            CURLOPT_HTTPHEADER => [
                                "cache-control: no-cache",
                                "postman-token: 29ef572a-4720-b8eb-fffb-201ab42dc303",
                            ],
                        ]);

                        $response1 = curl_exec($curl1);
                        $err1 = curl_error($curl1);

                        curl_close($curl1);

                        if ($err1) {
                            echo "cURL Error #:" . $err1;
                        } else {
                            //   echo 'image data'.gettype($response1);
                            $html = HtmlDomParser::str_get_html($response1);
                            $img = $html->getElementsByTagName("a");
                            $imgsrc = $img->getAttribute("href");
                            //  echo 'image data1'.$imgsrc;
                        }

                        $common_model->updatedata(
                            "trip_places",
                            ["image" => $imgsrc],
                            ["id" => $place2["id"]]
                        );
                    }
                }
            }
            if ($update) {
                $data["status"] = 1;
            } else {
                $data["status"] = 0;
            }
        }

        if (!empty($request["dataarray"][0]["place"])) {
            //   echo "static";
            foreach ($request["dataarray"] as $place) {
                $user_data = [
                    "trip_id" => $request->trip_id,
                    "title" => $place["title"],
                    "location" => $place["place"],
                    "description" => $place["desc"],
                    "lat" => $place["lat"],
                    "city" => "Trip To " . $place["city"],
                    "lng" => $place["lng"],
                    "type" => $place["type"],
                    "phone" => $place["phone"] ? $place["phone"] : "",
                    "website" => $place["website"] ? $place["website"] : "",
                    "basic_info" => $place["basic_info"]
                        ? $place["basic_info"]
                        : "",
                ];

                $insert = $common_model->insertdata("trip_places", $user_data);

                if (!empty($place["place_id"])) {
                    $curl = curl_init();

                    curl_setopt_array($curl, [
                        CURLOPT_URL =>
                            "https://maps.googleapis.com/maps/api/place/details/json?place_id=" .
                            $place["place_id"] .
                            "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => [
                            "cache-control: no-cache",
                            "postman-token: c6a8eca4-b200-b0ae-04ce-f1368e1a00d4",
                        ],
                    ]);

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    curl_close($curl);

                    if ($err) {
                        echo "cURL Error #:" . $err;
                    } else {
                        //   echo $response;
                        $placerefer = json_decode($response, true);
                        //   print_r($placerefer['result']['photos'][0]['photo_reference']);
                        //   echo $placerefer['result']['photos'][0]['photo_reference'];

                        if (array_key_exists("photos", $placerefer["result"])) {
                            $place_refer_id =
                                $placerefer["result"]["photos"][0][
                                    "photo_reference"
                                ];
                        } else {
                            $place_refer_id = "";
                        }

                        //   $place_refer_id = $placerefer['result']['photos'] ? $placerefer['result']['photos'][0]['photo_reference'] : '';
                    }

                    if ($place_refer_id != "") {
                        $curl1 = curl_init();

                        curl_setopt_array($curl1, [
                            CURLOPT_URL =>
                                "https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photo_reference=" .
                                $place_refer_id .
                                "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "GET",
                            CURLOPT_HTTPHEADER => [
                                "cache-control: no-cache",
                                "postman-token: 29ef572a-4720-b8eb-fffb-201ab42dc303",
                            ],
                        ]);

                        $response1 = curl_exec($curl1);
                        $err1 = curl_error($curl1);

                        curl_close($curl1);

                        if ($err1) {
                            echo "cURL Error #:" . $err1;
                        } else {
                            //   echo 'image data'.gettype($response1);
                            $html = HtmlDomParser::str_get_html($response1);
                            $img = $html->getElementsByTagName("a");
                            $imgsrc = $img->getAttribute("href");
                            //  echo 'image data1'.$imgsrc;
                        }

                        $common_model->updatedata(
                            "trip_places",
                            ["image" => $imgsrc],
                            ["id" => $insert]
                        );
                    }
                }
            }
            if ($insert) {
                $data["status"] = 1;
            } else {
                $data["status"] = 0;
            }
        }

        if (!empty($request["invite_array"])) {
            foreach ($request["invite_array"] as $invite) {
                $user_data = [
                    "trip_id" => $request->trip_id,
                    "from_id" => $request->uid,
                    "to_id" => $invite,
                ];
                $insert = $common_model->insertdata("invites", $user_data);
                if ($insert) {
                    $user_data1 = [
                        "post_id" => $request->trip_id,
                        "from_id" => $request->uid,
                        "to_id" => $invite,
                        "type" => "invite",
                    ];
                    $common_model->insertdata("notifications", $user_data1);
                }
            }
        }

        if (empty($data)) {
            $data["status"] = 2;
        }

        //   $data ='';
        return json_encode($data);
    }

    public function add_placesdataadmin(Request $request)
    {
        $common_model = new Common();

        if (!empty($request["dataarray"][0]["place"])) {
            //   echo "static";
            foreach ($request["dataarray"] as $place) {
                $user_data = [
                    "trip_id" => $request->trip_id,
                    "title" => $place["title"],
                    "location" => $place["place"],
                    "description" => $place["desc"],
                    "lat" => $place["lat"],
                    "city" => "Trip To " . $place["city"],
                    "lng" => $place["lng"],
                    "type" => $place["type"],
                    "phone" => $place["phone"] ? $place["phone"] : "",
                    "website" => $place["website"] ? $place["website"] : "",
                    "basic_info" => $place["basic_info"]
                        ? $place["basic_info"]
                        : "",
                ];

                $insert = $common_model->insertdata("trip_places", $user_data);

                if (!empty($place["place_id"])) {
                    $curl = curl_init();

                    curl_setopt_array($curl, [
                        CURLOPT_URL =>
                            "https://maps.googleapis.com/maps/api/place/details/json?place_id=" .
                            $place["place_id"] .
                            "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => [
                            "cache-control: no-cache",
                            "postman-token: c6a8eca4-b200-b0ae-04ce-f1368e1a00d4",
                        ],
                    ]);

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    curl_close($curl);

                    if ($err) {
                        echo "cURL Error #:" . $err;
                    } else {
                        //   echo $response;
                        $placerefer = json_decode($response, true);
                        //   print_r($placerefer['result']['photos'][0]['photo_reference']);
                        //   echo $placerefer['result']['photos'][0]['photo_reference'];

                        if (array_key_exists("photos", $placerefer["result"])) {
                            $place_refer_id =
                                $placerefer["result"]["photos"][0][
                                    "photo_reference"
                                ];
                        } else {
                            $place_refer_id = "";
                        }

                        //   $place_refer_id = $placerefer['result']['photos'] ? $placerefer['result']['photos'][0]['photo_reference'] : '';
                    }

                    if ($place_refer_id != "") {
                        $curl1 = curl_init();

                        curl_setopt_array($curl1, [
                            CURLOPT_URL =>
                                "https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photo_reference=" .
                                $place_refer_id .
                                "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "GET",
                            CURLOPT_HTTPHEADER => [
                                "cache-control: no-cache",
                                "postman-token: 29ef572a-4720-b8eb-fffb-201ab42dc303",
                            ],
                        ]);

                        $response1 = curl_exec($curl1);
                        $err1 = curl_error($curl1);

                        curl_close($curl1);

                        if ($err1) {
                            echo "cURL Error #:" . $err1;
                        } else {
                            //   echo 'image data'.gettype($response1);
                            $html = HtmlDomParser::str_get_html($response1);
                            $img = $html->getElementsByTagName("a");
                            $imgsrc = $img->getAttribute("href");
                            //  echo 'image data1'.$imgsrc;
                        }

                        $common_model->updatedata(
                            "trip_places",
                            ["image" => $imgsrc],
                            ["id" => $insert]
                        );
                    }
                }
            }
            // if($insert){
            //             $data['status'] = 1;

            // }
            //      else{
            //   $data['status'] = 0;
            // }
        }

        //   return json_encode($data);
    }

    function element_to_obj($element)
    {
        $obj = ["tag" => $element->tagName];
        foreach ($element->attributes as $attribute) {
            $obj[$attribute->name] = $attribute->value;
        }
        foreach ($element->childNodes as $subElement) {
            if ($subElement->nodeType == XML_TEXT_NODE) {
                $obj["html"] = $subElement->wholeText;
            } else {
                $obj["children"][] = element_to_obj($subElement);
            }
        }
        return $obj;
    }

    public function add_invitedata(Request $request)
    {
        $common_model = new Common();
        foreach ($request["invite_array"] as $invite) {
            $check_login = $common_model->getfirst("invites", [
                "trip_id" => $request->trip_id,
                "from_id" => $request->user_id,
                "to_id" => $invite,
            ]);
            if (empty($check_login)) {
                $user_data = [
                    "trip_id" => $request->trip_id,
                    "from_id" => $request->user_id,
                    "to_id" => $invite,
                ];

                $insert = $common_model->insertdata("invites", $user_data);
            } else {
                $insert = "";
            }
        }
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function update_userimage(Request $request)
    {
        $common_model = new Common();
        $file = $request->file("file");
        $image_name = "";
        if (!empty($file)) {
            //Move Uploaded File
            $destinationPath = "images";
            $original_name = $file->getClientOriginalName();
            $file_name = str_replace(
                " ",
                "_",
                time() . $file->getClientOriginalName()
            );
            if ($file->move($destinationPath, $file_name)) {
                $image_name = $file_name;
            }
        }

        $update = $common_model->updatedata(
            "users",
            ["image" => $image_name],
            ["id" => $request->id]
        );

        if ($update) {
            $check_login = $common_model->getfirst("users", [
                "status" => 1,
                "id" => $request->id,
            ]);
            $data["status"] = 1;
            $data["id"] = $check_login->id;
            $data["name"] = $check_login->name;
            $data["email"] = $check_login->email;
            $data["social_id"] = $check_login->social_id;
            $data["image"] = $check_login->image;
            $data["phone"] = $check_login->phone;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function update_userpassword(Request $request)
    {
        $common_model = new Common();
        $user = $common_model->getfirst("users", ["id" => $request->id]);
        $old_password = $request->old_password;
        $new_password = $request->password;

        if (Hash::check($old_password, $user->password)) {
            $update = $common_model->updatedata(
                "users",
                ["password" => Hash::make($new_password)],
                ["id" => $request->id]
            );
            if ($update) {
                $data["status"] = 1;
            } else {
                $data["status"] = 0;
            }
        } else {
            $data["status"] = 2;
        }
        return json_encode($data);
    }

    public function add_trip(Request $request)
    {
        $common_model = new Common();

        $user_data = [
            "where_to" => $request->where_to,
            "userid" => $request->uid,
            "start_date" => $request->s_date,
            "end_date" => $request->e_date,
            "lat" => $request->lat,
            "city" => "Trip To " . $request->city,
            "lng" => $request->lng,
            "description" => $request->description ? $request->description : "",

            "type" => "trip",
        ];

        $insert = $common_model->insertdata("trips", $user_data);

        if (!empty($request->place_id)) {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL =>
                    "https://maps.googleapis.com/maps/api/place/details/json?place_id=" .
                    $request->place_id .
                    "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "cache-control: no-cache",
                    "postman-token: c6a8eca4-b200-b0ae-04ce-f1368e1a00d4",
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                //   echo $response;
                $placerefer = json_decode($response, true);
                //   print_r($placerefer['result']['photos'][0]['photo_reference']);
                //   echo $placerefer['result']['photos'][0]['photo_reference'];

                //   $place_refer_id = $placerefer['result']['photos'] ? $placerefer['result']['photos'][0]['photo_reference'] : '';
                if (array_key_exists("photos", $placerefer["result"])) {
                    $place_refer_id =
                        $placerefer["result"]["photos"][0]["photo_reference"];
                } else {
                    $place_refer_id = "";
                }
            }

            if ($place_refer_id != "") {
                $curl1 = curl_init();

                curl_setopt_array($curl1, [
                    CURLOPT_URL =>
                        "https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photo_reference=" .
                        $place_refer_id .
                        "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => [
                        "cache-control: no-cache",
                        "postman-token: 29ef572a-4720-b8eb-fffb-201ab42dc303",
                    ],
                ]);

                $response1 = curl_exec($curl1);
                $err1 = curl_error($curl1);

                curl_close($curl1);

                if ($err1) {
                    echo "cURL Error #:" . $err1;
                } else {
                    //   echo 'image data'.gettype($response1);
                    $html = HtmlDomParser::str_get_html($response1);
                    $img = $html->getElementsByTagName("a");
                    $imgsrc = $img->getAttribute("href");
                    //  echo 'image data1'.$imgsrc;
                }

                $common_model->updatedata(
                    "trips",
                    ["image" => $imgsrc],
                    ["id" => $insert]
                );
            }
        }
        if ($insert) {
            $data["status"] = 1;
            $data["id"] = $insert;
            $tripid = $insert;
            if (!empty($request["invite_array"])) {
                foreach ($request["invite_array"] as $invite) {
                    $user_data = [
                        "trip_id" => $tripid,
                        "from_id" => $request->uid,
                        "to_id" => $invite,
                    ];
                    $insert = $common_model->insertdata("invites", $user_data);
                    if ($insert) {
                        $user_data1 = [
                            "post_id" => $tripid,
                            "from_id" => $request->uid,
                            "to_id" => $invite,
                            "type" => "invite",
                        ];
                        $common_model->insertdata("notifications", $user_data1);
                    }
                }
            }
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function add_followers(Request $request)
    {
        $common_model = new Common();

        $user_data = [
            "from_id" => $request->logged_id,
            "to_id" => $request->userid,
        ];

        $insert = $common_model->insertdata("followers", $user_data);
        if ($insert) {
            $user_data1 = [
                "from_id" => $request->logged_id,
                "to_id" => $request->userid,
                "type" => "follow",
            ];
            $common_model->insertdata("notifications", $user_data1);
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function add_likes(Request $request)
    {
        $common_model = new Common();
        $match = 0;
        $user_data = [
            "from_id" => $request->logged_id,
            "to_id" => $request->to_id,
            "status" => "1",
        ];
        $is_user = $common_model->getfirst("likes", [
            "from_id" => $request->to_id,
            "to_id" => $request->logged_id,
        ]);
        if (!empty($is_user)) {
            $user_data["match"] = 1;
            $match = "1";
            $user_data1 = [
                "from_id" => $request->logged_id,
                "to_id" => $request->to_id,
                "text" => "match",
            ];
            $common_model->insertdata("notifications", $user_data1);

            $userrrdata = $common_model->getfirst("users", [
                "id" => $request->to_id,
            ]);
            $userrrdata1 = $common_model->getfirst("users", [
                "id" => $request->logged_id,
            ]);
            if ($userrrdata->push_token) {
                $curl = curl_init();

                curl_setopt_array($curl, [
                    CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "{\r\n\r\n \"notification\": {\r\n\r\n  \"title\": \"You got a new link\",\r\n\r\n  \"body\": \"You found a new match with $userrrdata1->name\"\r\n\r\n },,\r\n \"data\": {\r\n \t\"type\": \"$request->route\",\r\n \t\"to_id\": $request->to_id,\r\n \t\"from_id\": $request->logged_id\r\n },\r\n\r\n \"to\" : \"$userrrdata->push_token\"\r\n\r\n}",
                    CURLOPT_HTTPHEADER => [
                        "authorization: key=AAAA3BARvB4:APA91bEPKLodaAag5vymEI1oIE8twcDqfvNFsIVNHg7WjLTXDicu32b5j3VqkeX4EnKS-84Bsp2MuYSswjW_0bJuz0nXEc_AQn1IYW19t1CJ-InQPquNGBgOwezSBTI_iYMbRVuDZsba",
                        "cache-control: no-cache",
                        "content-type: application/json",
                        "postman-token: af98a951-d6d7-6926-f0da-7a7601ff1082",
                    ],
                ]);

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    //   echo "cURL Error #:" . $err;
                } else {
                    //   echo $response;
                }
            }
        }

        $insert = $common_model->insertdata("likes", $user_data);
        if ($insert) {
            $data["status"] = 1;
            $data["is_match"] = $match;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function add_dislikes(Request $request)
    {
        if (Like::where([
                ['from_id', $request->logged_id],
                ['to_id', $request->to_id]
            ])->orWhere([
                ['from_id', $request->to_id],
                ['to_id', $request->logged_id]
            ])->where('status', 1)->exists()) {

            Like::where([
                ['from_id', $request->logged_id],
                ['to_id', $request->to_id]
            ])->orWhere([
                ['from_id', $request->to_id],
                ['to_id', $request->logged_id]
            ])->update(['status' => 0, 'created_at' => now()]);
            $data["status"] = 1;

        } else {
            $data["status"] = 0;
        }

        // $common_model = new Common();

        // $user_data = [
        //     "from_id" => $request->logged_id,
        //     "to_id" => $request->to_id,
        //     "status" => "0",
        // ];

        // $insert = $common_model->insertdata("likes", $user_data);
        // if ($insert) {
        //     $data["status"] = 1;
        // } else {
        //     $data["status"] = 0;
        // }

        return json_encode($data);
    }

    public function view_matcdataaa(Request $request)
    {
        $common_model = new Common();
        $userdataa = $common_model->getfirst("users", [
            "id" => $request->logged_id,
        ]);
        $touserdataa = $common_model->getfirst("users", [
            "id" => $request->to_id,
        ]);

        if (!empty($userdataa)) {
            $data["logged_username"] = $userdataa->name;
            $data["logged_userimage"] = $userdataa->image;
            $data["to_username"] = $touserdataa->name;
            $data["to_userimage"] = $touserdataa->image;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_matches(Request $request)
    {
        $common_model = new Common();
        // $mches = $common_model->get_matches($request->id);
        $mches = $common_model->selectdata("likes", ["match" => "1"]);

        $mid = [];
        foreach ($mches as $sb) {
            if ($sb->from_id == $request->id) {
                $sb->match_id = $sb->to_id;
                array_push($mid, $sb);
            } elseif ($sb->to_id == $request->id) {
                $sb->match_id = $sb->from_id;
                array_push($mid, $sb);
            } else {
                $mid = [];
            }
        }

        $mch_data = [];
        foreach ($mid as $md) {
            $md->userdata = $common_model->getfirst("users", [
                "id" => $md->match_id,
            ]);
            array_push($mch_data, $md);
        }

        if (!empty($mch_data)) {
            $data["result"] = $mch_data;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function add_comments(Request $request)
    {
        $common_model = new Common();

        $user_data = [
            "post_id" => $request->post_id,
            "user_id" => $request->user_id,
            "comment" => $request->comment,
        ];

        $insert = $common_model->insertdata("comments", $user_data);
        if ($insert) {
            $user_data1 = [
                "post_id" => $request->post_id,
                "from_id" => $request->user_id,
                "to_id" => $request->to_id,
                "type" => "comment",
            ];
            $common_model->insertdata("notifications", $user_data1);
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function add_chat(Request $request)
    {
        //   print_r($request->all());

        $file = $request->file("file");

        $common_model = new Common();

        $user_data = [
            "from_id" => $request->from_id,
            "to_id" => $request->to_id,
            "message" => $request->message,
        ];

        if (!empty($file)) {
            //Move Uploaded File
            $destinationPath = "audios";
            $original_name = $file->getClientOriginalName();
            $file_name = str_replace(
                " ",
                "_",
                time() . $file->getClientOriginalName()
            );
            if ($file->move($destinationPath, $file_name)) {
                $user_data["voice_message"] = $file_name;
            }
        }

        $insert = $common_model->insertdata("chats", $user_data);
        if ($insert) {
            $userrrdata = $common_model->getfirst("users", [
                "id" => $request->to_id,
            ]);
            $userrrdata1 = $common_model->getfirst("users", [
                "id" => $request->from_id,
            ]);
            if ($userrrdata->push_token) {
                $curl = curl_init();

                curl_setopt_array($curl, [
                    CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "{\r\n\r\n \"notification\": {\r\n\r\n  \"title\": \"New Message From $userrrdata1->name\",\r\n\r\n  \"body\": \"$request->message\"\r\n\r\n },,\r\n \"data\": {\r\n \t\"type\": \"$request->route\",\r\n \t\"to_id\": $request->to_id,\r\n \t\"from_id\": $request->from_id\r\n },\r\n\r\n \"to\" : \"$userrrdata->push_token\"\r\n\r\n}",
                    CURLOPT_HTTPHEADER => [
                        "authorization: key=AAAA3BARvB4:APA91bEPKLodaAag5vymEI1oIE8twcDqfvNFsIVNHg7WjLTXDicu32b5j3VqkeX4EnKS-84Bsp2MuYSswjW_0bJuz0nXEc_AQn1IYW19t1CJ-InQPquNGBgOwezSBTI_iYMbRVuDZsba",
                        "cache-control: no-cache",
                        "content-type: application/json",
                        "postman-token: af98a951-d6d7-6926-f0da-7a7601ff1082",
                    ],
                ]);

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    //   echo "cURL Error #:" . $err;
                } else {
                    //   echo $response;
                }
            }

            $data["status"] = 1;
            $data["id"] = $insert;
            $data["type"] = $request->route;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function testapi(Request $request)
    {
        $file = $request->file("file");
        $audio_name = "";
        if (!empty($file)) {
            //Move Uploaded File
            $destinationPath = "audios";
            $original_name = $file->getClientOriginalName();
            $file_name = str_replace(
                " ",
                "_",
                time() . $file->getClientOriginalName()
            );
            if ($file->move($destinationPath, $file_name)) {
                $audio_name = $file_name;
            }
        }
        print_r($audio_name);
        die();
        dd($request->file);
    }

    public function add_report(Request $request)
    {
        $common_model = new Common();
        $check_login = $common_model->getfirst("reports", [
            "post_id" => $request->post_id,
            "from_id" => $request->from_id,
            "to_id" => $request->to_id,
        ]);
        if (!empty($check_login)) {
            $data["status"] = 2;
        } else {
            $user_data = [
                "post_id" => $request->post_id,
                "from_id" => $request->from_id,
                "to_id" => $request->to_id,
                "reason" => $request->reason,
            ];

            $insert = $common_model->insertdata("reports", $user_data);
            if ($insert) {
                $data["status"] = 1;
            } else {
                $data["status"] = 0;
            }
        }

        return json_encode($data);
    }

    public function check_followers(Request $request)
    {
        $common_model = new Common();

        $check_login = $common_model->getfirst("followers", [
            "from_id" => $request->logged_id,
            "to_id" => $request->userid,
        ]);
        if (!empty($check_login)) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function delete_followers(Request $request)
    {
        $common_model = new Common();

        $user_data = [
            "from_id" => $request->logged_id,
            "to_id" => $request->userid,
        ];

        $insert = $common_model->deletedata("followers", [
            "from_id" => $request->logged_id,
            "to_id" => $request->userid,
        ]);
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function delete_followerdata(Request $request)
    {
        $common_model = new Common();

        $user_data = [
            "from_id" => $request->logged_id,
            "to_id" => $request->userid,
        ];

        $insert = $common_model->deletedata("followers", [
            "from_id" => $request->logged_id,
            "to_id" => $request->userid,
        ]);
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function delete_tripguide(Request $request)
    {
        $common_model = new Common();

        $insert = $common_model->deletedata("trips", ["id" => $request->id]);
        if ($insert) {
            $common_model->deletedata("trip_places", [
                "trip_id" => $request->id,
            ]);
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function delete_adminuser(Request $request)
    {
        $common_model = new Common();

        $insert = $common_model->deletedata("users", ["id" => $request->id]);
        if ($insert) {
            $common_model->deletedata("trips", ["userid" => $request->id]);
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function delete_placedata(Request $request)
    {
        $common_model = new Common();

        $insert = $common_model->deletedata("trip_places", [
            "id" => $request->id,
        ]);
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function delete_commentdata(Request $request)
    {
        $common_model = new Common();

        $insert = $common_model->deletedata("comments", ["id" => $request->id]);
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function disable_trips(Request $request)
    {
        $common_model = new Common();

        $insert = $common_model->updatedata(
            "trips",
            ["status" => "0"],
            ["id" => $request->id]
        );
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function update_chatread(Request $request)
    {
        $common_model = new Common();

        $insert = $common_model->updatedata(
            "chats",
            ["is_read" => "1"],
            ["to_id" => $request->to_id, "from_id" => $request->from_id]
        );
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function update_notiread(Request $request)
    {
        $common_model = new Common();

        $insert = $common_model->updatedata(
            "notifications",
            ["is_read" => "1"],
            ["to_id" => $request->id]
        );
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function enable_trips(Request $request)
    {
        $common_model = new Common();

        $insert = $common_model->updatedata(
            "trips",
            ["status" => "1"],
            ["id" => $request->id]
        );
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function delete_likes(Request $request)
    {
        $common_model = new Common();

        $insert = $common_model->deletedata("likes", [
            "post_id" => $request->post_id,
            "user_id" => $request->user_id,
        ]);
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function add_guide(Request $request)
    {
        $common_model = new Common();

        $user_data = [
            "where_to" => $request->where_to,
            "userid" => $request->uid,
            "lat" => $request->lat,
            "city" => "Trip To " . $request->city,
            "lng" => $request->lng,
            "description" => $request->description ? $request->description : "",
            "type" => "guide",
        ];

        $insert = $common_model->insertdata("trips", $user_data);

        if (!empty($request->place_id)) {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL =>
                    "https://maps.googleapis.com/maps/api/place/details/json?place_id=" .
                    $request->place_id .
                    "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "cache-control: no-cache",
                    "postman-token: c6a8eca4-b200-b0ae-04ce-f1368e1a00d4",
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                //   echo $response;
                $placerefer = json_decode($response, true);
                //   print_r($placerefer['result']['photos'][0]['photo_reference']);
                //   echo $placerefer['result']['photos'][0]['photo_reference'];

                //   $place_refer_id = $placerefer['result']['photos'] ? $placerefer['result']['photos'][0]['photo_reference'] : '';
                if (array_key_exists("photos", $placerefer["result"])) {
                    $place_refer_id =
                        $placerefer["result"]["photos"][0]["photo_reference"];
                } else {
                    $place_refer_id = "";
                }
            }

            if ($place_refer_id != "") {
                $curl1 = curl_init();

                curl_setopt_array($curl1, [
                    CURLOPT_URL =>
                        "https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photo_reference=" .
                        $place_refer_id .
                        "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => [
                        "cache-control: no-cache",
                        "postman-token: 29ef572a-4720-b8eb-fffb-201ab42dc303",
                    ],
                ]);

                $response1 = curl_exec($curl1);
                $err1 = curl_error($curl1);

                curl_close($curl1);

                if ($err1) {
                    echo "cURL Error #:" . $err1;
                } else {
                    //   echo 'image data'.gettype($response1);
                    $html = HtmlDomParser::str_get_html($response1);
                    $img = $html->getElementsByTagName("a");
                    $imgsrc = $img->getAttribute("href");
                    //  echo 'image data1'.$imgsrc;
                }

                $common_model->updatedata(
                    "trips",
                    ["image" => $imgsrc],
                    ["id" => $insert]
                );
            }
        }

        if ($insert) {
            $data["status"] = 1;
            $data["id"] = $insert;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function update_guidedata(Request $request)
    {
        $common_model = new Common();

        $user_data = [
            "where_to" => $request->where_to,
            "userid" => $request->uid,
            "lat" => $request->lat,
            "lng" => $request->lng,
            "description" => $request->description,
        ];

        if (strpos($request->city, "Trip To") !== false) {
            $user_data["city"] = $request->city;
        } else {
            $user_data["city"] = "Trip To " . $request->city;
        }

        $insert = $common_model->updatedata("trips", $user_data, [
            "id" => $request->id,
        ]);

        if (!empty($request->place_id)) {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL =>
                    "https://maps.googleapis.com/maps/api/place/details/json?place_id=" .
                    $request->place_id .
                    "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "cache-control: no-cache",
                    "postman-token: c6a8eca4-b200-b0ae-04ce-f1368e1a00d4",
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                //   echo $response;
                $placerefer = json_decode($response, true);
                //   print_r($placerefer['result']['photos'][0]['photo_reference']);
                //   echo $placerefer['result']['photos'][0]['photo_reference'];

                //   $place_refer_id = $placerefer['result']['photos'] ? $placerefer['result']['photos'][0]['photo_reference'] : '';
                if (array_key_exists("photos", $placerefer["result"])) {
                    $place_refer_id =
                        $placerefer["result"]["photos"][0]["photo_reference"];
                } else {
                    $place_refer_id = "";
                }
            }

            if ($place_refer_id != "") {
                $curl1 = curl_init();

                curl_setopt_array($curl1, [
                    CURLOPT_URL =>
                        "https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photo_reference=" .
                        $place_refer_id .
                        "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => [
                        "cache-control: no-cache",
                        "postman-token: 29ef572a-4720-b8eb-fffb-201ab42dc303",
                    ],
                ]);

                $response1 = curl_exec($curl1);
                $err1 = curl_error($curl1);

                curl_close($curl1);

                if ($err1) {
                    echo "cURL Error #:" . $err1;
                } else {
                    //   echo 'image data'.gettype($response1);
                    $html = HtmlDomParser::str_get_html($response1);
                    $img = $html->getElementsByTagName("a");
                    $imgsrc = $img->getAttribute("href");
                    //  echo 'image data1'.$imgsrc;
                }

                $common_model->updatedata(
                    "trips",
                    ["image" => $imgsrc],
                    ["id" => $request->id]
                );
            }
        }

        if ($insert) {
            $data["status"] = 1;
            $data["id"] = $insert;
        } else {
            $data["status"] = 1;
        }

        return json_encode($data);
    }

    public function update_tripdata(Request $request)
    {
        $common_model = new Common();

        $user_data = [
            "where_to" => $request->where_to,
            "userid" => $request->uid,
            "lat" => $request->lat,
            "lng" => $request->lng,
            "start_date" => $request->start_date,
            "end_date" => $request->end_date,
            "description" => $request->description,
        ];

        if (strpos($request->city, "Trip To") !== false) {
            $user_data["city"] = $request->city;
        } else {
            $user_data["city"] = "Trip To " . $request->city;
        }

        $insert = $common_model->updatedata("trips", $user_data, [
            "id" => $request->id,
        ]);

        if (!empty($request->place_id)) {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL =>
                    "https://maps.googleapis.com/maps/api/place/details/json?place_id=" .
                    $request->place_id .
                    "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "cache-control: no-cache",
                    "postman-token: c6a8eca4-b200-b0ae-04ce-f1368e1a00d4",
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                //   echo $response;
                $placerefer = json_decode($response, true);
                //   print_r($placerefer['result']['photos'][0]['photo_reference']);
                //   echo $placerefer['result']['photos'][0]['photo_reference'];

                //   $place_refer_id = $placerefer['result']['photos'] ? $placerefer['result']['photos'][0]['photo_reference'] : '';
                if (array_key_exists("photos", $placerefer["result"])) {
                    $place_refer_id =
                        $placerefer["result"]["photos"][0]["photo_reference"];
                } else {
                    $place_refer_id = "";
                }
            }

            if ($place_refer_id != "") {
                $curl1 = curl_init();

                curl_setopt_array($curl1, [
                    CURLOPT_URL =>
                        "https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photo_reference=" .
                        $place_refer_id .
                        "&key=AIzaSyDnMvJXKTsrCcDRdM03l8TlIdlYZuIXQHs",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => [
                        "cache-control: no-cache",
                        "postman-token: 29ef572a-4720-b8eb-fffb-201ab42dc303",
                    ],
                ]);

                $response1 = curl_exec($curl1);
                $err1 = curl_error($curl1);

                curl_close($curl1);

                if ($err1) {
                    echo "cURL Error #:" . $err1;
                } else {
                    //   echo 'image data'.gettype($response1);
                    $html = HtmlDomParser::str_get_html($response1);
                    $img = $html->getElementsByTagName("a");
                    $imgsrc = $img->getAttribute("href");
                    //  echo 'image data1'.$imgsrc;
                }

                $common_model->updatedata(
                    "trips",
                    ["image" => $imgsrc],
                    ["id" => $request->id]
                );
            }
        }

        if ($insert) {
            $data["status"] = 1;
            $data["id"] = $insert;
        } else {
            $data["status"] = 1;
        }

        return json_encode($data);
    }

    public function get_triplist(Request $request)
    {
        $common_model = new Common();
        $trip = $common_model->selectdata(
            "trips",
            ["userid" => $request->id, "type" => "trip"],
            ["id" => "desc"]
        );

        $alldatass = [];
        foreach ($trip as $tr) {
            $tr->total_places = $common_model->getcount("trip_places", [
                ["trip_id", "=", $tr->id],
                ["type", "=", "trip"],
                ["location", "!=", ""],
            ]);
            array_push($alldatass, $tr);
        }
        if (!empty($alldatass)) {
            $data["result"] = $alldatass;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_faqlist(Request $request)
    {
        $common_model = new Common();
        $faqs = $common_model->selectdata("faq");

        if (!empty($faqs)) {
            $data["result"] = $faqs;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_chat_users(Request $request)
    {
        $common_model = new Common();
        //  echo 'chatuser1111'.$request->user_id;
        $chatusers = $common_model->get_chatusers($request->user_id);

        $all_from_users = [];
        $alldatass = [];
        foreach ($chatusers as $key => $user) {
            // print_r($user);
            $from_user = $user->from_id;
            if ($from_user == $request->user_id) {
                $from_user = $user->to_id;
            }

            //  echo 'fromid'.$from_user;
            $user_data = $common_model->getfirst("users", [
                ["id", "=", $from_user],
            ]);

            if (!empty($user_data) && !in_array($from_user, $all_from_users)) {
                $allsusers = $common_model->selectdata("chats", [
                    "to_id" => $request->user_id,
                    "from_id" => $user_data->id,
                    "is_read" => "0",
                ]);
                $user->read_count = count($allsusers);
                $user->sender_id = $user_data->id;
                $user->sender_name = $user_data->name;
                $user->sender_email = $user_data->email;
                $user->sender_image = $user_data->image;
                array_push($all_from_users, $from_user);
                array_push($alldatass, $user);
            }
            // print_r($all_from_users);
        }

        if (!empty($alldatass)) {
            $data["result"] = $alldatass;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_chat_usersbk(Request $request)
    {
        $common_model = new Common();
        $chatusers = $common_model->get_chatusers($request->user_id);
        $all_from_users = [];
        $alldatass = [];
        foreach ($chatusers as $user) {
            $from_user = $user->from_id;
            if ($from_user == $request->user_id) {
                $from_user = $user->to_id;
            }
            $user_data = $common_model->getfirst("users", [
                ["id", "=", $from_user],
            ]);

            if (!empty($user_data) && !in_array($from_user, $all_from_users)) {
                $user->sender_id = $user_data->id;
                $user->sender_name = $user_data->name;
                $user->sender_email = $user_data->email;
                $user->sender_image = $user_data->image;
                array_push($all_from_users, $from_user);
                array_push($alldatass, $user);
            }
        }
        if (!empty($alldatass)) {
            $data["result"] = $alldatass;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_chats(Request $request)
    {
        $common_model = new Common();
        $user_details = $common_model->getfirst("users", [
            "id" => $request->to_id,
        ]);
       
        $userscnt = $common_model->get_chatsall(
            $request->to_id,
            $request->from_id
        );
        $users = $common_model->get_chats(
            $request->to_id,
            $request->from_id,
            $request->page_no
        );       

        if (!empty($users)) {
            $data["result"] = $users;
            $data["user_id"] = $user_details->id;
            $data["user_name"] = $user_details->name;
            $data["user_email"] = $user_details->email;
            $data["user_image"] = $user_details->image;
            $data["status"] = 1;
            $data["Total_messages"] = count($userscnt);
        } else {
            $data["status"] = 0;
            $data["user_name"] = $user_details->name;
        }
        return json_encode($data);
    }

    public function get_alluserlist(Request $request)
    {
        $common_model = new Common();
        $users = $common_model->selectdata("users", [
            ["id", "!=", $request->id],
        ]);

        if (!empty($users)) {
            $data["result"] = $users;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_alladminuserlist(Request $request)
    {
        $common_model = new Common();
        $users = $common_model->selectdata(
            "users",
            ["status" => "1"],
            ["id" => "desc"]
        );

        if (!empty($users)) {
            $data["result"] = $users;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_commentlist(Request $request)
    {
        $common_model = new Common();
        $comment = $common_model->selectdata(
            "comments",
            ["post_id" => $request->post_id],
            ["id" => "asc"]
        );

        $alldatass = [];
        foreach ($comment as $tr) {
            $userdata = $common_model->getfirst("users", [
                "id" => $tr->user_id,
            ]);
            $tr->username = $userdata->name;
            $tr->userimage = $userdata->image;
            array_push($alldatass, $tr);
        }
        if (!empty($alldatass)) {
            $data["result"] = $alldatass;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_triphomelist(Request $request)
    {
        $common_model = new Common();
        $trip = $common_model->gethomedatacount($request->id);
        $tripss = $common_model->gethomedata($request->id, $request->page_no);

        $checkprivacy = [];
        foreach ($tripss as $tr1) {
            if ($tr1->type == "trip" && $tr1->view_trip == "Everyone") {
                //   echo '1';
                array_push($checkprivacy, $tr1);
            }
            if ($tr1->type == "guide" && $tr1->view_post == "Everyone") {
                //   echo '2';
                array_push($checkprivacy, $tr1);
            }
            if ($tr1->type == "trip" && $tr1->view_trip == "Followers") {
                // echo '3';
                $userdata = $common_model->getfirst("followers", [
                    "to_id" => $tr1->userID,
                ]);
                if (!empty($userdata)) {
                    array_push($checkprivacy, $tr1);
                }
            }
            if ($tr1->type == "guide" && $tr1->view_post == "Followers") {
                //   echo '4';
                $userdata1 = $common_model->getfirst("followers", [
                    "to_id" => $tr1->userID,
                ]);
                if (!empty($userdata1)) {
                    array_push($checkprivacy, $tr1);
                }
            }
        }

        $checkprivacy1 = [];
        foreach ($trip as $tr12) {
            if ($tr12->type == "trip" && $tr12->view_trip == "Everyone") {
                //   echo '1';
                array_push($checkprivacy1, $tr12);
            }
            if ($tr12->type == "guide" && $tr12->view_post == "Everyone") {
                //   echo '2';
                array_push($checkprivacy1, $tr12);
            }
            if ($tr12->type == "trip" && $tr12->view_trip == "Followers") {
                // echo '3';
                $userdata2 = $common_model->getfirst("followers", [
                    "to_id" => $tr12->userID,
                ]);
                if (!empty($userdata2)) {
                    array_push($checkprivacy1, $tr12);
                }
            }
            if ($tr12->type == "guide" && $tr12->view_post == "Followers") {
                //   echo '4';
                $userdata12 = $common_model->getfirst("followers", [
                    "to_id" => $tr12->userID,
                ]);
                if (!empty($userdata12)) {
                    array_push($checkprivacy1, $tr12);
                }
            }
        }

        $alldatass = [];
        foreach ($checkprivacy as $tr) {
            $tr->like_count = $common_model->getcount("likes", [
                "post_id" => $tr->id,
            ]);
            $tr->comment_count = $common_model->getcount("comments", [
                "post_id" => $tr->id,
            ]);
            array_push($alldatass, $tr);
        }
        if (!empty($alldatass)) {
            $data["result"] = $alldatass;
            $data["total"] = count($checkprivacy1);
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_likecommentlist(Request $request)
    {
        $common_model = new Common();
        $likelist = $common_model->getlikesuserdata($request->id);
        $commentlist = $common_model->getcommentuserdata($request->id);

        if (!empty($likelist) || !empty($commentlist)) {
            $data["likelist"] = $likelist;
            $data["commentlist"] = $commentlist;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_guideadminlist(Request $request)
    {
        $common_model = new Common();
        $trip = $common_model->getguideadmindata();

        $alldatass = [];
        foreach ($trip as $tr) {
            $tr->like_count = $common_model->getcount("likes", [
                "post_id" => $tr->id,
            ]);
            $tr->comment_count = $common_model->getcount("comments", [
                "post_id" => $tr->id,
            ]);
            array_push($alldatass, $tr);
        }
        if (!empty($alldatass)) {
            $data["result"] = $alldatass;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_tripadminlist(Request $request)
    {
        $common_model = new Common();
        $trip = $common_model->gettripadmindata();

        if (!empty($trip)) {
            $data["result"] = $trip;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_guidelist(Request $request)
    {
        $common_model = new Common();
        $guide = $common_model->selectdata(
            "trips",
            ["userid" => $request->id, "type" => "guide"],
            ["id" => "desc"]
        );

        $alldatass = [];
        foreach ($guide as $tr) {
            $tr->like_count = $common_model->getcount("likes", [
                "post_id" => $tr->id,
            ]);
            $tr->comment_count = $common_model->getcount("comments", [
                "post_id" => $tr->id,
            ]);
            array_push($alldatass, $tr);
        }

        if (!empty($alldatass)) {
            $data["result"] = $alldatass;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_likeids(Request $request)
    {
        $common_model = new Common();
        $guide = $common_model->selectdata("likes", [
            "user_id" => $request->user_id,
        ]);
        if (!empty($guide)) {
            $data["status"] = 1;
            $data["result"] = $guide;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_tripplaceslist(Request $request)
    {
        $common_model = new Common();
        $guide = $common_model->selectdata("trip_places", [
            ["trip_id", "=", $request->id],
            ["type", "=", $request->type],
            ["location", "!=", ""],
        ]);
        if (!empty($guide)) {
            $data["result"] = $guide;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function view_usertripdetails(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->gettripuserdata(
            $request->id,
            $request->type
        );

        $like_count = $common_model->getcount("likes", [
            "post_id" => $check_token->id,
        ]);
        $comment_count = $common_model->getcount("comments", [
            "post_id" => $check_token->id,
        ]);

        if (!empty($check_token)) {
            $data["result"] = $check_token;
            $data["like_count"] = $like_count;
            $data["comment_count"] = $comment_count;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_followerlist(Request $request)
    {
        $common_model = new Common();
        $alldatas = [];
        $sub = $common_model->selectdata("followers", [
            "to_id" => $request->id,
        ]);
        foreach ($sub as $sb) {
            $sb->userdata = $common_model->getfirst("users", [
                "id" => $sb->from_id,
            ]);
            array_push($alldatas, $sb);
        }
        if (!empty($alldatas)) {
            $data["result"] = $alldatas;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_followerids(Request $request)
    {
        $common_model = new Common();
        $sub = $common_model->selectdata("followers", [
            "from_id" => $request->user_id,
        ]);

        if (!empty($sub)) {
            $data["result"] = $sub;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_followinglist(Request $request)
    {
        $common_model = new Common();
        $alldatas = [];
        $sub = $common_model->selectdata("followers", [
            "from_id" => $request->id,
        ]);
        foreach ($sub as $sb) {
            $sb->userdata = $common_model->getfirst("users", [
                "id" => $sb->to_id,
            ]);
            array_push($alldatas, $sb);
        }
        if (!empty($alldatas)) {
            $data["result"] = $alldatas;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function admin_do_login(Request $request)
    {
        if (!empty($request->email) && !empty($request->password)) {
            $common_model = new Common();
            $check_login = $common_model->getfirst("admins", [
                "status" => 1,
                "email" => $request->email,
            ]);
            if (!empty($check_login)) {
                if (Hash::check($request->password, $check_login->password)) {
                    Auth::logout();
                    $data["status"] = 1;
                    $data["id"] = $check_login->id;
                    $data["name"] = $check_login->name;
                    $data["email"] = $check_login->email;
                    $data["image"] = $check_login->image;
                    $data["phone"] = $check_login->phone;
                } else {
                    $data["status"] = 0;
                }
            } else {
                $data["status"] = 0;
            }
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function check_admin_token(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("admins", [
            "id" => $request->userId,
        ]);
        if (!empty($check_token)) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function alldashboard_count(Request $request)
    {
        $common_model = new Common();
        $total_users = $common_model->getcount("users", [["status", "=", "1"]]);
        $total_trips = $common_model->getcount("trips", [
            "status" => "1",
            "type" => "trip",
        ]);
        $total_guides = $common_model->getcount("trips", [
            "status" => "1",
            "type" => "guide",
        ]);
        if (!empty($total_users)) {
            $data["total_users"] = $total_users;
        }
        if (!empty($total_trips)) {
            $data["total_trips"] = $total_trips;
        }
        if (!empty($total_guides)) {
            $data["total_guides"] = $total_guides;
        }

        return json_encode($data);
    }
    public function get_adminprofile(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("admins", [
            "id" => $request->id,
        ]);
        if (!empty($check_token)) {
            $data["result"] = $check_token;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_countries()
    {
        $common_model = new Common();
        $country = $common_model->selectdata("country", ["parent" => 0]);
        if (!empty($country)) {
            $data["result"] = $country;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }
    public function get_states(Request $request)
    {
        $common_model = new Common();
        $state = $common_model->selectdata("country", [
            ["parent", "=", $request->id],
        ]);
        if (!empty($state)) {
            $data["result"] = $state;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function update_adminprofile(Request $request)
    {
        $common_model = new Common();
        $file = $request->file("file");
        $image_name = "";
        if (!empty($file)) {
            //Move Uploaded File
            $destinationPath = "images";
            $original_name = $file->getClientOriginalName();
            $file_name = str_replace(
                " ",
                "_",
                time() . $file->getClientOriginalName()
            );
            if ($file->move($destinationPath, $file_name)) {
                $image_name = $file_name;
            }
        }
        $user_data = [
            "name" => $request->name,

            "email" => $request->email,
            "phone" => $request->phone,
            "address" => $request->address,
            "lat" => $request->lat,
            "lng" => $request->lng,
            // 'username' => $request->username
        ];
        if (!empty($image_name)) {
            $user_data["image"] = $image_name;
        }
        $update = $common_model->updatedata("admins", $user_data, [
            "id" => $request->id,
        ]);
        if ($update) {
            $data["status"] = 1;
            $data["name"] = $request->name;
            $data["email"] = $request->email;
            if (!empty($image_name)) {
                $data["image"] = $image_name;
            } else {
                $data["image"] = $request->profile_image;
            }
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_contactdata(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("contact_us", ["id" => 1]);

        if (!empty($check_token)) {
            $data["result"] = $check_token;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function update_privacysetting(Request $request)
    {
        $common_model = new Common();
        $user_data = [
            "view_post" => $request->view_post,
            "view_trip" => $request->view_trip,
            "message_me" => $request->message_me,
            "comment_me" => $request->comment_me,
        ];

        $update = $common_model->updatedata("users", $user_data, [
            "id" => $request->id,
        ]);
        if ($update) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function update_currentlocation(Request $request)
    {
        $common_model = new Common();
        $user_data = [
            "current_location" => $request->current_location,
            "current_lat" => $request->current_lat,
            "current_lng" => $request->current_lng,
        ];

        $update = $common_model->updatedata("users", $user_data, [
            "id" => $request->id,
        ]);
        if ($update) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function update_pushtoken(Request $request)
    {
        $common_model = new Common();
        $user_data = [
            "push_token" => $request->push_token,
        ];

        $update = $common_model->updatedata("users", $user_data, [
            "id" => $request->user_id,
        ]);
        if ($update) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function update_adminpassword(Request $request)
    {
        if (!empty($request->oldpassword)) {
            $common_model = new Common();
            $user = $common_model->getfirst("admins", ["id" => $request->id]);
            $old_password = $request->oldpassword;
            $new_password = $request->password;

            if (Hash::check($old_password, $user->password)) {
                $update = $common_model->updatedata(
                    "admins",
                    ["password" => Hash::make($new_password)],
                    ["id" => $request->id]
                );
                if ($update) {
                    $data["status"] = 1;
                } else {
                    $data["status"] = 0;
                }
            } else {
                $data["status"] = 2;
            }
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_podcastsetting(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("podcast_settings", ["id" => 1]);

        if (!empty($check_token)) {
            $data["result"] = $check_token;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function update_faq(Request $request)
    {
        $common_model = new Common();
        $user_data = [
            "question" => $request->question,
            "answer" => $request->answer,
        ];

        $update = $common_model->updatedata("faq", $user_data, [
            "id" => $request->id,
        ]);
        if ($update) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function view_faqdetails(Request $request)
    {
        $common_model = new Common();
        $faqdata = $common_model->getfirst("faq", ["id" => $request->id]);

        if (!empty($faqdata)) {
            $data["result"] = $faqdata;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function delete_faq(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->deletedata("faq", ["id" => $request->id]);

        if (!empty($check_token)) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function add_movies(Request $request)
    {
        $common_model = new Common();
        $file = $request->file("file");
        $image_name = "";
        if (!empty($file)) {
            //Move Uploaded File
            $destinationPath = "images";
            $original_name = $file->getClientOriginalName();
            $file_name = str_replace(
                " ",
                "_",
                time() . $file->getClientOriginalName()
            );
            if ($file->move($destinationPath, $file_name)) {
                $image_name = $file_name;
            }
        }
        $user_data = [
            "title" => $request->title,
            "date" => $request->dates,
            "category" => $request->category,
            "description" => $request->description,
            "rating" => $request->rating,
        ];
        if (!empty($image_name)) {
            $user_data["image"] = $image_name;
        }
        $insert = $common_model->insertdata("movies", $user_data);
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function view_reportdta(Request $request)
    {
        $common_model = new Common();
        $rprtss = $common_model->getfirst("reports", ["id" => $request->id]);
        $trpss = $common_model->getfirst("trips", ["id" => $rprtss->post_id]);
        $toemail = $common_model->getfirst("users", ["id" => $rprtss->to_id]);
        $fromemail = $common_model->getfirst("users", [
            "id" => $rprtss->from_id,
        ]);
        if (!empty($rprtss)) {
            $data["status"] = 1;
            $data["report_data"] = $rprtss;
            $data["trip_title"] = $trpss->city;
            $data["trip_type"] = $trpss->type;
            $data["from_email"] = $fromemail->email;
            $data["to_email"] = $toemail->email;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_reportdta(Request $request)
    {
        $common_model = new Common();
        $alldatas = [];
        $rprts = $common_model->getreportdata();
        foreach ($rprts as $rp) {
            $email = $common_model->getfirst("users", ["id" => $rp->to_id]);
            $rp->to_email = $email->email;
            array_push($alldatas, $rp);
        }
        if (!empty($alldatas)) {
            $data["status"] = 1;
            $data["result"] = $alldatas;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_movies(Request $request)
    {
        $common_model = new Common();
        $alldatas = [];
        $movies = $common_model->selectdata(
            "movies",
            ["status" => "1"],
            ["id" => "desc"]
        );
        foreach ($movies as $mv) {
            $mv->dates = Carbon::parse($mv->date)->format("F d, Y");
            array_push($alldatas, $mv);
        }
        if (!empty($alldatas)) {
            $data["result"] = $alldatas;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_homecards(Request $request)
    {
        $likeUserIds = Like::where('from_id', $request->id)->where('status', 1)->pluck('to_id')->toArray();
        // $dislikeUserIds = Like::where('from_id', $request->id)
        //     ->where('status', 0)
        //     ->whereDate('created_at', now()->subDays(7)->toDateTimeString())
        //     ->pluck('to_id')
        //     ->toArray();
        // $userIds = array_merge($likeUserIds, $dislikeUserIds);        
        $users = User::where('id', '!=', $request->id)
            ->whereNotIn('id', $likeUserIds)
            ->where('is_subscribe', 1)
            ->orderby('id', 'desc')
            ->get();
        $authUserType = User::where('id', $request->id)->pluck('user_type')->first();
        $userArr = [];
        foreach ($users as $user) {
            if ($user->user_type == $authUserType) {
                $user['total_match'] = getUserMatch($request->id, $user->id);
                $user['allimages'] = DB::table('images')->where('user_id', $user->id)->get();
                $userArr[] = $user;
            }
        }
        return response()->json([
            'status' => true,
            'data' => $userArr
        ]);

        // $common_model = new Common();
        // //  echo $request->id;
        // // $fdata = $common_model->getfilterdata($request->selected_gender, $request->min_avg,$request->max_avg,$request->lat, $request->lng,$request->distance,$request->id);
        // $likidss = [];
        // $check_token = $common_model->getfirst("users", ["id" => $request->id]);
        // $fdata = $common_model->getallcardhomedata($request->id);
        // $likedids = $common_model->selectdata("likes", [
        //     "from_id" => $request->id,
        // ]);
        // foreach ($likedids as $lids) {
        //     array_push($likidss, $lids->to_id);
        // }
        // // print_r($likedids);
        // $alldatass = [];
        // foreach ($fdata as $usr) {
        //     if (!empty($check_token->my_interest) && !empty($usr->my_interest)) {
        //         $a = $check_token->my_interest;
        //         $b = $usr->my_interest;
        //         $array1 = self::stringToArray($a);
        //         $array2 = self::stringToArray($b);

        //         $result = array_intersect($array1, $array2);
        //         $totalcount = count($array1);
        //         $matchcount = count($result);

        //         $mtchdata = ($matchcount / $totalcount) * 100;
        //         $usr->total_match = round($mtchdata);
        //     } else {
        //         // echo "else";
        //         $usr->total_match = 0;
        //     }
        //     if ($likidss) {
        //         if (in_array($usr->id, $likidss, true)) {
                    
        //         } else {
        //             $usr->allimages = $common_model->selectdata("images", [
        //                 "user_id" => $usr->id,
        //             ]);
        //             array_push($alldatass, $usr);
        //         }
        //     } else {
        //         $usr->allimages = $common_model->selectdata("images", [
        //             "user_id" => $usr->id,
        //         ]);
        //         array_push($alldatass, $usr);
        //     }
        // }

        // if (!empty($alldatass)) {
        //     $data["status"] = 1;
        //     $data["result"] = $alldatass;
        // } else {
        //     $data["status"] = 0;
        // }

        // return json_encode($data);
    }

    public function stringToArray($string)
    {
        $temp1 = ltrim($string, "[");
        $temp2 = rtrim($temp1, "]");

        $return = [];

        $loop = explode(",", $temp2);

        foreach ($loop as $key => $value) {
            array_push($return, $value);
        }

        return $return;
    }
    public function get_notifilist(Request $request)
    {
        $common_model = new Common();
        $comment = $common_model->selectdata(
            "notifications",
            ["to_id" => $request->id],
            ["id" => "desc"]
        );

        $alldatass = [];
        foreach ($comment as $tr) {
            $tr->userdata = $common_model->getfirst("users", [
                "id" => $tr->from_id,
            ]);
            array_push($alldatass, $tr);
        }
        if (!empty($alldatass)) {
            $data["result"] = $alldatass;
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_notificount(Request $request)
    {
        $common_model = new Common();
        $comment = $common_model->selectdata(
            "notifications",
            ["to_id" => $request->id, "is_read" => "0"],
            ["id" => "desc"]
        );

        if (!empty($comment)) {
            $data["noti_count"] = count($comment);
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
            $data["noti_count"] = 0;
        }
        return json_encode($data);
    }

    public function remove_match(Request $request)
    {
        $common_model = new Common();

        $user_data = [
            "from_id" => $request->from_id,
            "to_id" => $request->to_id,
        ];
        $insert = $common_model->deletedata("likes", [
            "from_id" => $request->from_id,
            "to_id" => $request->to_id,
        ]);
        $common_model->updatedata(
            "likes",
            ["match" => "0"],
            ["from_id" => $request->to_id, "to_id" => $request->from_id]
        );
        $common_model->delete_matchuserchat($request->to_id, $request->from_id);

        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function get_homefilter(Request $request)
    {
        $common_model = new Common();
        //  dd($request->all());
        $filterdata = $common_model->getfilterdata(
            $request->selected_gender,
            $request->min_age,
            $request->max_age,
            $request->lat,
            $request->lng,
            $request->distance,
            $request->user_id,
            $request->user_type
        );
        $allfdatass = [];
        $likidss = [];
        $check_token = $common_model->getfirst("users", [
            "id" => $request->user_id,
        ]);
        $likedids = $common_model->selectdata("likes", [
            "from_id" => $request->user_id,
        ]);
        foreach ($likedids as $lids) {
            array_push($likidss, $lids->to_id);
        }
        if ($filterdata) {
            foreach ($filterdata as $usr) {
                if (
                    !empty($check_token->my_interest) &&
                    !empty($usr->my_interest)
                ) {
                    $a = $check_token->my_interest;
                    $b = $usr->my_interest;
                    $array1 = self::stringToArray($a);
                    $array2 = self::stringToArray($b);

                    $result = array_intersect($array1, $array2);
                    $totalcount = count($array1);
                    $matchcount = count($result);

                    $mtchdata = ($matchcount / $totalcount) * 100;
                    $usr->total_match = round($mtchdata);
                } else {
                    // echo "else";
                    $usr->total_match = 0;
                }
                if ($likidss) {
                    if (in_array($usr->id, $likidss, true)) {
                    } else {
                        $usr->allimages = $common_model->selectdata("images", [
                            "user_id" => $usr->id,
                        ]);
                        array_push($allfdatass, $usr);
                    }
                } else {
                    $usr->allimages = $common_model->selectdata("images", [
                        "user_id" => $usr->id,
                    ]);
                    array_push($allfdatass, $usr);
                }
            }
        }

        if ($allfdatass) {
            $data["status"] = 1;
            $data["result"] = $allfdatass;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function view_movies(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("movies", [
            "id" => $request->id,
        ]);

        if (!empty($check_token)) {
            $data["result"] = $check_token;
            $data["date"] = Carbon::parse($check_token->date)->format("F d, Y");
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function update_movies(Request $request)
    {
        $common_model = new Common();
        $file = $request->file("file");
        $image_name = "";
        if (!empty($file)) {
            //Move Uploaded File
            $destinationPath = "images";
            $original_name = $file->getClientOriginalName();
            $file_name = str_replace(
                " ",
                "_",
                time() . $file->getClientOriginalName()
            );
            if ($file->move($destinationPath, $file_name)) {
                $image_name = $file_name;
            }
        }
        $user_data = [
            "title" => $request->title,
            "date" => $request->dates,
            "category" => $request->category,
            "description" => $request->description,
            "rating" => $request->rating,
        ];
        if (!empty($image_name)) {
            $user_data["image"] = $image_name;
        }
        $insert = $common_model->updatedata("movies", $user_data, [
            "id" => $request->id,
        ]);
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function delete_reportdata(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->deletedata("reports", [
            "id" => $request->id,
        ]);

        if (!empty($check_token)) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function delete_movies(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->deletedata("movies", [
            "id" => $request->id,
        ]);

        if (!empty($check_token)) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_allreview(Request $request)
    {
        $common_model = new Common();
        $review = $common_model->selectdata(
            "review",
            ["status" => "1"],
            ["id" => "desc"]
        );
        if (!empty($review)) {
            $data["result"] = $review;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_limituser(Request $request)
    {
        $common_model = new Common();

        $userss = $common_model->selectdata(
            "users",
            ["status" => "1"],
            ["id" => "desc"],
            0,
            5
        );
        //     foreach($moviess as $mv){
        //       $mv->dates = Carbon::parse($mv->date)->format('F d, Y');
        //       array_push($allmovie,$mv);
        //   }
        if (!empty($userss)) {
            $data["status"] = 1;
            $data["result"] = $userss;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_viewreview(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("review", [
            "id" => $request->id,
        ]);

        if (!empty($check_token)) {
            $data["result"] = $check_token;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_podcast(Request $request)
    {
        $common_model = new Common();
        $alldatas = [];
        $podcast = $common_model->selectdata(
            "podcast",
            ["status" => "1"],
            ["id" => "desc"]
        );
        foreach ($podcast as $pc) {
            $pc->dates = Carbon::parse($pc->date)->format("F d, Y");
            if ($pc->video_type == "link") {
                preg_match(
                    '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i',
                    $pc->youtube_link,
                    $match
                );
                $vid = $match[1];
                $pc->thumbnail = "http://img.youtube.com/vi/" . $vid . "/1.jpg";
            }
            array_push($alldatas, $pc);
        }
        if (!empty($alldatas)) {
            $data["result"] = $alldatas;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function delete_podcast(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->deletedata("podcast", [
            "id" => $request->id,
        ]);

        if (!empty($check_token)) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function view_podcast(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("podcast", [
            "id" => $request->id,
        ]);

        if (!empty($check_token)) {
            $data["result"] = $check_token;
            $data["date"] = Carbon::parse($check_token->date)->format("F d, Y");
            if ($check_token->video_type == "link") {
                preg_match(
                    '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i',
                    $check_token->youtube_link,
                    $match
                );
                $vid = $match[1];
                $data["videourl"] = "https://www.youtube.com/embed/" . $vid;
            } else {
                $data["videourl"] = "";
            }
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function update_podcast(Request $request)
    {
        $common_model = new Common();
        $file = $request->file("file");
        $image_name = "";
        if (!empty($file)) {
            //Move Uploaded File
            $destinationPath = "images";
            $original_name = $file->getClientOriginalName();
            $file_name = str_replace(
                " ",
                "_",
                time() . $file->getClientOriginalName()
            );
            if ($file->move($destinationPath, $file_name)) {
                $image_name = $file_name;
            }
        }
        $user_data = [
            "title" => $request->title,
            "date" => $request->dates,
            "youtube_link" => $request->youtube_link,
            "description" => $request->description,
            "rating" => $request->rating,
            "video_type" => $request->video_type,
        ];
        if (!empty($image_name)) {
            $user_data["video"] = $image_name;
        }
        $insert = $common_model->updatedata("podcast", $user_data, [
            "id" => $request->id,
        ]);
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function add_podcast(Request $request)
    {
        $common_model = new Common();
        $file = $request->file("file");
        $image_name = "";
        if (!empty($file)) {
            //Move Uploaded File
            $destinationPath = "images";
            $original_name = $file->getClientOriginalName();
            $file_name = str_replace(
                " ",
                "_",
                time() . $file->getClientOriginalName()
            );
            if ($file->move($destinationPath, $file_name)) {
                $image_name = $file_name;
            }
        }
        $user_data = [
            "title" => $request->title,
            "date" => $request->dates,
            "youtube_link" => $request->youtube_link,
            "description" => $request->description,
            "rating" => $request->rating,
            "video_type" => $request->video_type,
        ];
        if (!empty($image_name)) {
            $user_data["video"] = $image_name;
        }
        $insert = $common_model->insertdata("podcast", $user_data);
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_pagesdata(Request $request)
    {
        $common_model = new Common();
        $pages = $common_model->selectdata(
            "pages",
            ["status" => "1"],
            ["id" => "desc"]
        );
        if (!empty($pages)) {
            $data["result"] = $pages;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function view_pagesdata(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("pages", ["id" => $request->id]);

        if (!empty($check_token)) {
            $data["result"] = $check_token;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function update_pages(Request $request)
    {
        $common_model = new Common();
        $user_data = [
            "title" => $request->title,

            "content" => $request->content,
        ];

        $update = $common_model->updatedata("pages", $user_data, [
            "id" => $request->id,
        ]);
        if ($update) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function forgot_password(Request $request)
    {
        if (!empty($request->email)) {
            $common_model = new Common();
            $check = $common_model->getfirst("users", [
                "status" => 1,
                "email" => $request->email,
            ]);
            if (!empty($check)) {
                $digits = 4;
                $token = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
                $common_model->updatedata(
                    "users",
                    ["token" => $token],
                    ["id" => $check->id]
                );
                $reset_link = $token;
                $email_data = [
                    "username" => $check->name,
                    "reset_link" => $reset_link,
                ];
                $email_to = $check->email;
                $name_to = $check->name;
                Mail::send("emails.forgot", $email_data, function (
                    $message
                ) use ($name_to, $email_to) {
                    $message
                        ->to($email_to, $name_to)
                        ->subject("Reset Password - Link Up");
                    $message->from("developerindiit@gmail.com", "Link Up");
                });
                $data["status"] = 1;
                $data["id"] = $check->id;
            } else {
                $data["status"] = 0;
            }
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function forgot_adminpassword(Request $request)
    {
        if (!empty($request->email)) {
            $common_model = new Common();
            $check = $common_model->getfirst("admins", [
                "status" => 1,
                "email" => $request->email,
            ]);
            if (!empty($check)) {
                $digits = 4;
                $token = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
                $common_model->updatedata(
                    "admins",
                    ["otp_token" => $token],
                    ["id" => $check->id]
                );
                $reset_link = $token;
                $email_data = [
                    "username" => $check->name,
                    "reset_link" => $reset_link,
                ];
                $email_to = $check->email;
                $name_to = $check->name;
                Mail::send("emails.forgotadmin", $email_data, function (
                    $message
                ) use ($name_to, $email_to) {
                    $message
                        ->to($email_to, $name_to)
                        ->subject("Reset Password - Travel Admin");
                    $message->from("002userdemo@gmail.com", "Travel Admin");
                });
                $data["status"] = 1;
                $data["id"] = $check->id;
            } else {
                $data["status"] = 2;
            }
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function verifyotp(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("users", [
            "token" => $request->otp,
        ]);
        if (!empty($check_token)) {
            $data["status"] = 1;
            $data["id"] = $check_token->id;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function verify_userotp(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("users", [
            "token" => $request->otp,
            "id" => $request->id,
        ]);
        if (!empty($check_token)) {
            $common_model->updatedata(
                "users",
                ["token" => "", "status" => "1"],
                ["id" => $request->id]
            );
            $data["status"] = 1;
            $data["id"] = $check_token->id;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function verify_adminotp(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("admins", [
            "otp_token" => $request->otp,
        ]);
        if (!empty($check_token)) {
            $data["status"] = 1;
            $data["id"] = $check_token->id;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function set_password(Request $request)
    {
        if (!empty($request->password)) {
            $common_model = new Common();
            $user = $common_model->getfirst("users", ["id" => $request->id]);
            $old_password = $request->oldpassword;
            if (Hash::check($old_password, $user->password)) {
                $update = $common_model->updatedata(
                    "users",
                    ["password" => Hash::make($request->password)],
                    ["id" => $request->id]
                );
                if ($update) {
                    $data["status"] = 1;
                } else {
                    $data["status"] = 0;
                }
            } else {
                $data["status"] = 2;
            }
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function set_adminpassword(Request $request)
    {
        if (!empty($request->password)) {
            $common_model = new Common();
            $update = $common_model->updatedata(
                "admins",
                [
                    "password" => Hash::make($request->password),
                    "otp_token" => "",
                ],
                ["id" => $request->id]
            );
            if ($update) {
                $data["status"] = 1;
            } else {
                $data["status"] = 0;
            }
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function reset_userpassword(Request $request)
    {
        if (!empty($request->password)) {
            $common_model = new Common();
            $update = $common_model->updatedata(
                "users",
                ["password" => Hash::make($request->password), "token" => ""],
                ["id" => $request->id]
            );
            if ($update) {
                $data["status"] = 1;
            } else {
                $data["status"] = 0;
            }
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function update_pagesimage(Request $request)
    {
        $common_model = new Common();
        $file = $request->file("file");
        $image_name = "";
        if (!empty($file)) {
            //Move Uploaded File
            $destinationPath = "images";
            $original_name = $file->getClientOriginalName();
            $file_name = str_replace(
                " ",
                "_",
                time() . $file->getClientOriginalName()
            );
            if ($file->move($destinationPath, $file_name)) {
                $image_name = $file_name;
            }
        }
        $user_data = [];
        if (!empty($image_name)) {
            $user_data["image"] = $image_name;
        }

        $update = $common_model->updatedata("pages", $user_data, [
            "id" => $request->id,
        ]);
        if ($update) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    public function get_myprofiledata(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("users", ["id" => $request->id]);
        $allimags = $common_model->selectdata("images", [
            "user_id" => $request->id,
        ]);
        $packdata = $common_model->getfirst("packages", [
            "user_id" => $request->id,
        ]);
        $mches = $common_model->selectdata("likes", ["match" => "1"]);
        $match_id = [];
        foreach ($mches as $sb) {
            if ($sb->from_id == $request->id) {
                array_push($match_id, "1");
            } elseif ($sb->to_id == $request->id) {
                array_push($match_id, "1");
            } else {
            }
        }

        if (!empty($match_id)) {
            $is_mtch = "1";
        } else {
            $is_mtch = "0";
        }

        if (!empty($check_token)) {
            $data["status"] = 1;
            $data["result"] = $check_token;
            $data["interests"] = MyInterest::where('user_id', $request->id)->pluck('interest_id')->toArray();
            $data["is_match"] = $is_mtch;
            $data["images"] = $allimags;
            $data["plan_data"] = $packdata;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_userpackdata(Request $request)
    {
        $common_model = new Common();
        $packdata = $common_model->getfirst("packages", [
            "user_id" => $request->id,
        ]);
        $packdata->paypal_token = User::where('id', $request->id)->pluck('paypal_token')->first();
        if (!empty($packdata)) {
            $data["status"] = 1;
            $data["result"] = $packdata;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_otherprofiledata(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("users", ["id" => $request->id]);
        $allimags = $common_model->selectdata("images", [
            "user_id" => $request->id,
        ]);

        $mches = $common_model->selectdata("likes", ["match" => "1"]);
        $match_id = [];
        foreach ($mches as $sb) {
            if ($sb->from_id == $request->id) {
                array_push($match_id, "1");
            } elseif ($sb->to_id == $request->id) {
                array_push($match_id, "1");
            } else {
            }
        }

        //   print_r($match_id);

        if (!empty($match_id)) {
            $is_mtch = "1";
        } else {
            $is_mtch = "0";
        }

        if (!empty($check_token)) {
            $data["status"] = 1;
            $data["result"] = $check_token;
            $data["is_match"] = $is_mtch;
            $data["images"] = $allimags;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function update_socialsetting(Request $request)
    {
        $common_model = new Common();
        $user_data = [
            "facebook" => $request->facebook,
            "google" => $request->google,
            "twitter" => $request->twitter,
            "youtube" => $request->youtube,

            "rss" => $request->rss,
        ];

        $update = $common_model->updatedata("social_setting", $user_data, [
            "id" => 1,
        ]);
        if ($update) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }

        return json_encode($data);
    }

    // app data funtions

    public function view_userdetails(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("users", ["id" => $request->id]);
        $total_trip = $common_model->getcount("trips", [
            "userid" => $request->id,
            "type" => "trip",
        ]);
        $total_guide = $common_model->getcount("trips", [
            "userid" => $request->id,
            "type" => "guide",
        ]);
        $total_region = $common_model->getcount("map_region", [
            "user_id" => $request->id,
        ]);
        $total_following = $common_model->getcount("followers", [
            "from_id" => $request->id,
        ]);
        $total_follower = $common_model->getcount("followers", [
            "to_id" => $request->id,
        ]);

        if (!empty($check_token)) {
            $data["result"] = $check_token;
            $data["trip_count"] = $total_trip;
            $data["following_count"] = $total_following;
            $data["follower_count"] = $total_follower;
            $data["total_guide"] = $total_guide;
            $data["total_region"] = $total_region;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function view_userdetailsadmin(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("users", ["id" => $request->id]);
        $total_trip = $common_model->getcount("trips", [
            "userid" => $request->id,
            "type" => "trip",
        ]);
        $total_following = $common_model->getcount("followers", [
            "from_id" => $request->id,
        ]);
        $total_follower = $common_model->getcount("followers", [
            "to_id" => $request->id,
        ]);

        $followerlist = [];
        $sub = $common_model->selectdata("followers", [
            "to_id" => $request->id,
        ]);
        foreach ($sub as $sb) {
            $sb->userdata = $common_model->getfirst("users", [
                "id" => $sb->from_id,
            ]);
            array_push($followerlist, $sb);
        }

        $followinglist = [];
        $sub1 = $common_model->selectdata("followers", [
            "from_id" => $request->id,
        ]);
        foreach ($sub1 as $sb1) {
            $sb1->userdata = $common_model->getfirst("users", [
                "id" => $sb1->to_id,
            ]);
            array_push($followinglist, $sb1);
        }

        if (!empty($check_token)) {
            $data["result"] = $check_token;
            $data["trip_count"] = $total_trip;
            $data["following_count"] = $total_following;
            $data["follower_count"] = $total_follower;
            $data["follower_list"] = $followerlist;
            $data["following_list"] = $followinglist;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function view_allmovies(Request $request)
    {
        $common_model = new Common();
        $alldatas = [];
        $mcount = $common_model->selectdata("movies", ["status" => "1"]);
        $movie_count = count($mcount);
        $movies = $common_model->selectdata(
            "movies",
            ["status" => "1"],
            ["id" => "desc"],
            $request->page_no,
            8
        );
        foreach ($movies as $mv) {
            $mv->dates = Carbon::parse($mv->date)->format("F d, Y");
            if (strlen($mv->description) > 100) {
                $mv->desc = substr($mv->description, 0, 100) . ".....";
            } else {
                $mv->desc = $mv->description;
            }
            array_push($alldatas, $mv);
        }
        if (!empty($alldatas)) {
            $data["result"] = $alldatas;
            $data["total"] = $movie_count;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function view_moviesdetails(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("movies", [
            "id" => $request->id,
        ]);

        if (!empty($check_token)) {
            $data["result"] = $check_token;
            $data["date"] = Carbon::parse($check_token->date)->format("F d, Y");
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function view_relatedmoviesdetails(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->relatedmovies(
            $request->id,
            $request->title
        );

        if (!empty($check_token)) {
            $data["result"] = $check_token;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function view_podcastdetails(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("podcast", [
            "id" => $request->id,
        ]);

        if (!empty($check_token)) {
            $data["result"] = $check_token;
            $data["date"] = Carbon::parse($check_token->date)->format("F d, Y");
            if ($check_token->video_type == "link") {
                preg_match(
                    '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i',
                    $check_token->youtube_link,
                    $match
                );
                $vid = $match[1];
                $data["videourl"] = "https://www.youtube.com/embed/" . $vid;
            } else {
                $data["videourl"] = "";
            }
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function view_allpodcasts(Request $request)
    {
        $common_model = new Common();
        $allsdatas = [];
        $mcount = $common_model->selectdata("podcast", ["status" => "1"]);
        $movie_count = count($mcount);
        $species = $common_model->selectdata(
            "podcast",
            ["status" => "1"],
            ["id" => "desc"],
            $request->page_no,
            8
        );
        foreach ($species as $sp) {
            $sp->dates = Carbon::parse($sp->date)->format("F d, Y");
            if ($sp->video_type == "link") {
                preg_match(
                    '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i',
                    $sp->youtube_link,
                    $match
                );
                $vid = $match[1];
                $sp->thumbnail = "http://img.youtube.com/vi/" . $vid . "/1.jpg";
            }
            if (strlen($sp->description) > 100) {
                $sp->desc = substr($sp->description, 0, 100) . ".....";
            } else {
                $sp->desc = $sp->description;
            }
            array_push($allsdatas, $sp);
        }
        if (!empty($allsdatas)) {
            $data["result"] = $allsdatas;
            $data["total"] = $movie_count;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }
    public function view_allpodcastsettingdata(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("podcast_settings", [
            "id" => $request->id,
        ]);

        if (!empty($check_token)) {
            $data["result"] = $check_token;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function view_allsocialsettingdata(Request $request)
    {
        $common_model = new Common();
        $check_token = $common_model->getfirst("social_setting", [
            "id" => $request->id,
        ]);

        if (!empty($check_token)) {
            $data["result"] = $check_token;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function get_allcountry()
    {
        $common_model = new Common();
        $country = $common_model->selectdata("country", ["parent" => 0]);
        if (!empty($country)) {
            $data["result"] = $country;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function add_faq(Request $request)
    {
        $common_model = new Common();
        $user_data = [
            "question" => $request->question,
            "answer" => $request->answer,
        ];

        $insert = $common_model->insertdata("faq", $user_data);
        if ($insert) {
            $data["status"] = 1;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function view_filtersmovies(Request $request)
    {
        $common_model = new Common();
        $alldatas = [];
        $fmoviescount = $common_model->moreloadmovies(
            $request->min_rate,
            $request->max_rate,
            $request->cats_name
        );
        $fcount = count($fmoviescount);
        $movies = $common_model->getfilterdata(
            $request->min_rate,
            $request->max_rate,
            $request->cats_name,
            $request->page_no
        );
        foreach ($movies as $mv) {
            $mv->dates = Carbon::parse($mv->date)->format("F d, Y");
            array_push($alldatas, $mv);
        }
        if (!empty($alldatas)) {
            $data["result"] = $alldatas;
            $data["ftotal"] = $fcount;
        } else {
            $data["status"] = 0;
        }
        return json_encode($data);
    }

    public function terms()
    {
        $terms = Term::get();
        return response()->json([
            'status' => count($terms) > 0 ? true : false,
            'data' => $terms,
        ]);
    }

    public function privacyPolicy()
    {
        $privacyPolicy = PrivacyPolicy::get();
        return response()->json([
            'status' => count($privacyPolicy) > 0 ? true : false,
            'data' => $privacyPolicy,
        ]);
    }

    public function contactUs(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $request['name'] = $user->name;
        $request['email'] = $user->email;
        if (ContactUs::create($request->all())) {
            return response()->json([
                'status' => true,
                'message' => 'Contact us form has been submitted!',
            ]);
        }
    }

    public function logout()
    {
        Session::pull("admin_looged_in");
        Session::pull("admin_id");
        Session::pull("admin_name");
        Session::pull("admin_image");
        return redirect("/");
    }
}
