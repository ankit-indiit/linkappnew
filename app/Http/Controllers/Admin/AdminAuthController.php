<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Notifications\AdminResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Admin;

class AdminAuthController extends Controller
{	
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function index()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {    	        
        $validator = Validator::make($request->all(), [
           'email' => 'required',
            'password' => 'required',
        ]);
        
        if ($validator->fails()) {
            return back()->with('error', $validator->messages()->first());
        }

        if (!Admin::where('email', $request->email)->exists()) {
            return back()->with('error', "User does not exist");
        }

        if (auth()->guard('admin')->attempt([
            'email' => $request->input('email'), 
            'password' => $request->input('password')
        ])) {
            // $user = auth()->guard('admin')->user();       
            return redirect()->route('dashboard')->with('success','Logged in successfully');
            
        } else {
            return back()->with('error','Invalid credentials');
        }
    }

    public function logout()
    {
        auth()->guard('admin')->logout();
        return redirect()->route('login')->with('success','Logged out successfully');      
    }

    public function profile(Request $request, $id)
    {
    	$admin = Admin::where('id', $id)->first();
    	return view('admin.profile', compact('admin'));
    }

    public function update(Request $request)
    {
    	if (isset($request->confirm_password)) {
	    	if (!Hash::check($request->current_password, $user = auth()->guard('admin')->user()->password)) {
	    		return response()->json([
		    		'status' => false,
		    		'message' => 'Your current password is invalid!',
		    	]);
	    	}
    		$request['password'] = Hash::make($request->confirm_password);
    		Admin::where('id', $request->id)->update($request->except('_token', 'current_password', 'new_password', 'confirm_password'));
    	} else {
    		Admin::where('id', $request->id)->update($request->except('_token', 'current_password', 'new_password', 'confirm_password', 'password'));
    	}
    	return response()->json([
    		'status' => true,
    		'message' => 'Profile has been updated!',
    	]);
    }

    public function profileImage(Request $request)
    {
    	if ($request->file('image')) {
            $image = $request->file('image');
            $imagename = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('assets/img');
            $image->move($destinationPath, $imagename);
            Admin::where('id', $request->id)->update([
                'image' => $imagename,
            ]);
            // if ($request->image_name) {                
            //     unlink("assets/img/".$request->image_name);
            // }
        }
        return response()->json([
            'status' => true,
            'image' => asset("public/assets/img/$imagename"),
            'image_name' => $imagename,
            'message' => 'Profile picture has been uploaded!',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        return view('admin.forgot-password');
    }

    public function forgotPasswordEmail(Request $request)
    {
        if (!Admin::where('email', $request->email)->exists()) {
            return redirect()->back()->with('error', 'This email does not exists in our system!');
        }
        $admin = Admin::where('email', $request->email)->first();
        $admin->notify(new AdminResetPassword($admin->id, $admin->name));
        return redirect()->back()->with('success', 'Reset password mail has been sent!');
    }

    public function setPasswordForm(Request $request, $id)
    {        
        return view('admin.set-password', compact('id'));
    }

    public function setPassword(Request $request)
    {        
        $password = Hash::make($request->confirm_password);
        Admin::where('id', $request->id)->update([
            'password' => $password
        ]);
        return redirect()->route('login')->with('success', 'Password has been successfully updated!');
    }
}