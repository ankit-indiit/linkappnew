<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminMessagesController;
use App\Http\Controllers\Admin\AdminContactUsController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\Admin\AdminNotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/clear', function() {
	$exitCode = Artisan::call('cache:clear');  
		echo '<h1>Cache facade value cleared</h1>';
	$exitCode = Artisan::call('route:clear');
		echo '<h1>Route cache cleared</h1>';
	$exitCode = Artisan::call('view:clear');
		echo '<h1>View cache cleared</h1>';
	$exitCode = Artisan::call('config:cache');
		return '<h1>Clear Config cleared</h1>';   
});
Route::get('/test', function() {
	return 'ok';
});

Route::group(['prefix' => 'admin', 'middleware' => ['adminauth']], function() {
	Route::get('/',[DashboardController::class, 'index'])->name('dashboard');
	Route::get('/update-interest',[DashboardController::class, 'updateInterest']);
	Route::get('/profile',[AdminAuthController::class, 'profile'])->name('adminProfile');
	Route::post('/update-profile',[AdminAuthController::class, 'update'])->name('updateProfile');
	Route::post('/update-profile-image',[AdminAuthController::class, 'profileImage'])->name('updateProfileImage');

	Route::resource('users', Admin\AdminUsersController::class);
	Route::resource('terms', Admin\AdminTermsController::class);
	Route::resource('privacy-policy', Admin\AdminPrivacyPolicyController::class);
	Route::resource('subscription', Admin\AdminSubscriptionController::class);
	Route::get('/messages',[AdminMessagesController::class, 'index'])->name('messages.index');
	Route::get('/messages/{toId}/{fromId}',[AdminMessagesController::class, 'show'])->name('messages.show');
	Route::get('/contact-us',[AdminContactUsController::class, 'index'])->name('contact-us.index');
	Route::get('/transaction',[AdminTransactionController::class, 'index'])->name('transaction.index');
	Route::get('/notification',[AdminNotificationController::class, 'index'])->name('notification.index');
});
Route::group(['prefix' => 'admin'], function() {
	Route::get('/login',[AdminAuthController::class, 'index'])->name('login');
	Route::post('/login',[AdminAuthController::class, 'login'])->name('doLogin');
	Route::post('logout', [AdminAuthController::class, 'logout'])->name('logOut');
	Route::get('forgot-password', [AdminAuthController::class, 'forgotPassword'])->name('admin.forgot-password');
	Route::post('forgot-password', [AdminAuthController::class, 'forgotPasswordEmail'])->name('admin.forgot-password-email');
	Route::get('set-password/{id}', [AdminAuthController::class, 'setPasswordForm'])->name('admin.set-password-form');
	Route::post('set-password', [AdminAuthController::class, 'setPassword'])->name('admin.set-password');	
});
 // App api
Route::post('/register_user', 'Front@register_user')->name('register_user');
Route::post('/check_email', 'Front@check_email')->name('check_email');
Route::post('/login_user', 'Front@login_user')->name('login_user');
Route::post('/get_myprofiledata', 'Front@get_myprofiledata')->name('get_myprofiledata');
Route::post('/forgot_password', 'Front@forgot_password')->name('forgot_password');
Route::post('/verifyotp', 'Front@verifyotp')->name('verifyotp');
Route::post('/set_password', 'Front@set_password')->name('set_password');
Route::post('/update_userpassword', 'Front@update_userpassword')->name('update_userpassword');
Route::post('/contact-us', 'Front@contactUs')->name('contactUs');
Route::post('/get_chat_users', 'Front@get_chat_users')->name('get_chat_users');
Route::post('/get_chats', 'Front@get_chats')->name('get_chats');
Route::post('/add_chat', 'Front@add_chat')->name('add_chat');
Route::post('/get_myprofiledata', 'Front@get_myprofiledata')->name('get_myprofiledata');
Route::post('/upload_profileimg', 'Front@upload_profileimg')->name('upload_profileimg');
Route::post('/delete_imags', 'Front@delete_imags')->name('delete_imags');
Route::post('/upload_allimg', 'Front@upload_allimg')->name('upload_allimg');
Route::post('/upload_uservideo', 'Front@upload_uservideo')->name('upload_uservideo');
Route::post('/update_userprofile', 'Front@update_userprofile')->name('update_userprofile');
Route::post('/get_homecards', 'Front@get_homecards')->name('get_homecards');
Route::post('/get_homefilter', 'Front@get_homefilter')->name('get_homefilter');
Route::post('/signup_user', 'Front@signup_user')->name('signup_user');
Route::post('/verify_userotp', 'Front@verify_userotp')->name('verify_userotp');
Route::post('/update_userdata', 'Front@update_userdata')->name('update_userdata');
Route::post('/upload_multiimg', 'Front@upload_multiimg')->name('upload_multiimg');
Route::post('/update_pushtoken', 'Front@update_pushtoken')->name('update_pushtoken');
Route::post('/update_chatread', 'Front@update_chatread')->name('update_chatread');
Route::post('/add_likes', 'Front@add_likes')->name('add_likes');
Route::post('/add_dislikes', 'Front@add_dislikes')->name('add_dislikes');
Route::post('/view_matcdataaa', 'Front@view_matcdataaa')->name('view_matcdataaa');
Route::post('/get_matches', 'Front@get_matches')->name('get_matches');
Route::post('/remove_match', 'Front@remove_match')->name('remove_match');
Route::post('/get_notifilist', 'Front@get_notifilist')->name('get_notifilist');
Route::post('/get_notificount', 'Front@get_notificount')->name('get_notificount');
Route::post('/update_notiread', 'Front@update_notiread')->name('update_notiread');
Route::post('/reset_userpassword', 'Front@reset_userpassword')->name('reset_userpassword');
Route::post('/update_currentlocation', 'Front@update_currentlocation')->name('update_currentlocation'); 
Route::post('/get_otherprofiledata', 'Front@get_otherprofiledata')->name('get_otherprofiledata'); 
Route::post('/get_packageplan', 'Front@get_packageplan')->name('get_packageplan'); 
Route::post('/add_paymentdataa', 'Front@add_paymentdataa')->name('add_paymentdataa'); 
Route::post('/get_userpackdata', 'Front@get_userpackdata')->name('get_userpackdata'); 
Route::get('/terms', 'Front@terms')->name('terms'); 
Route::get('/privacy-policy', 'Front@privacyPolicy')->name('privacyPolicy'); 
Route::post('/testapi', 'Front@testapi')->name('testapi'); 
