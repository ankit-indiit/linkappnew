<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>Link</title>
        <link rel="shortcut icon" href="{{ asset('public/assets/img/favicon.png') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/plugins/bootstrap/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/plugins/fontawesome/css/fontawesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/plugins/fontawesome/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/css/bootstrap-datetimepicker.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables/datatables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/plugins/fullcalendar/fullcalendar.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/css/animate.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/css/nice-select.css') }}">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.2.13/dist/semantic.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Spartan:wght@300;400;500;600;700&display=swap">
        <link href="{{ asset('public/assets/css/summernote-bs4.min.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('public/assets/css/admin.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/css/responsive.css') }}">
    </head>
    <body>        
        <div class="main-wrapper">
            <div class="login-page">
                <div class="login-body container">
                    <div class="loginbox">
                        <div class="login-right-wrap">
                            <div class="account-header">
                                <div class="account-logo text-center mb-4">
                                    <a href="index.html">
                                    <img src="{{ asset('public/assets/img/logo.png') }}" alt="" class="img-fluid">
                                    </a>
                                </div>
                            </div>
                            <div class="login-header text-center">
                                <h3><strong>Reset Password</strong></h3>
                            </div>
                            <form action="{{ route('admin.set-password') }}" method="post" id="adminResetPasswordForm">
                            	@csrf                                                                
                                <div class="form-group mb-4">
                                    <label class="control-label">Password</label>
                                    <input class="form-control" type="password" name="password" id="password" placeholder="Enter your Password">
                                </div>
                                <div class="form-group mb-4">
                                    <label class="control-label">Confirm Password</label>
                                    <input class="form-control" type="password" name="confirm_password" placeholder="Enter your Confirm Password">
                                </div>
                                <input type="hidden" name="id" value="{{ $id }}">
                                <div class="text-center">
                                    <button class="btn btn-primary btn-block account-btn" type="submit">Reset Password</button>
                                </div>
                                <div class="text-center forgotpass mt-4">
                                    <a href="{{ route('login') }}">Back to Login</a>
                                </div>   
                            </form>                                                    
                        </div>
                    </div>
                </div>
            </div>
        </div>        
        <script src="{{ asset('public/assets/js/jquery.min.js') }}"></script>
        <script src="{{ asset('public/assets/js/popper.min.js') }}"></script>
        <script src="{{ asset('public/assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('public/assets/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
        <script src="{{ asset('public/assets/js/moment.min.js') }}"></script>
        <script src="{{ asset('public/assets/js/bootstrap-datetimepicker.min.js') }}"></script>
        <script src="{{ asset('public/assets/plugins/datatables/datatables.min.js') }}"></script>
        <script src="{{ asset('public/assets/js/jquery-ui.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.3.1/fullcalendar.js"></script>
        <script src="{{ asset('public/assets/plugins/fullcalendar/jquery.fullcalendar.js') }}"></script>
        <script src="{{ asset('public/assets/js/jquery.nice-select.min.js') }}"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/semantic-ui@2.2.13/dist/semantic.min.js"></script>
        <script src="{{ asset('public/assets/js/admin.js') }}"></script>
        <script src="{{ asset('public/assets/js/summernote-bs4.min.js') }}"></script>   
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <script src="{{ asset('public/assets/js/jquery.validate.min.js') }}"></script>
        <script type="text/javascript">
            $("#adminResetPasswordForm").validate({
                rules: {                    
                    password: {
                        // required: true,
                        strong_password: true,
                    },                     
                    confirm_password: {
                        required: function(element) {
                           return $('#password').is(':filled');
                        },
                        minlength: 5,
                        equalTo: '[name="password"]'
                    },                  
                },
                messages: {
                    // password: "Please enter password!",
                    confirm_password: {
                        required: "Please enter confirm password!",  
                        minlength: "Length must be at least 5 characters!",  
                        equalTo: "Password and confirm password must be matched!",  
                    },  
                }               
            });

            $.validator.addMethod("strong_password", function (value, element) {
                let password = value;
                if (!(/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@#$%&])(.{8,20}$)/.test(password))) {
                    return false;
                }
                return true;
            }, function (value, element) {
                let password = $(element).val();
                if (!(/^(.{8,20}$)/.test(password))) {
                    return 'Password length should be minimum 8 characters';
                }
                else if (!(/^(?=.*[A-Z])/.test(password))) {
                    return 'Password must contain at least one uppercase.';
                }
                else if (!(/^(?=.*[a-z])/.test(password))) {
                    return 'Password must contain at least one lowercase.';
                }
                else if (!(/^(?=.*[0-9])/.test(password))) {
                    return 'Password must contain at least one digit.';
                }
                else if (!(/^(?=.*[@#$%&])/.test(password))) {
                    return "Password must contain special characters from @#$%&.";
                }
                return false;
            });
            @if (Session::has('success'))
                swal({
                  position: 'top-end',
                  icon: 'success',
                  title: "{{ Session::get('success') }}",
                  showConfirmButton: false,
                  // timer: 1500
                })
            @endif
        	@if (Session::has('error'))
        		swal({
				  icon: 'error',
				  title: 'Oops...',
				  text: "{{ Session::get('error') }}",
				  footer: '<a href="">Why do I have this issue?</a>'
				})
        	@endif
        </script>     
    </body>
</html>