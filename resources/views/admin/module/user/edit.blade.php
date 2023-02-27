@extends('admin.layout.master')
@section('content')
<div class="page-wrapper">
	<div class="content container-fluid">
		<div class="page-header">
			<div class="row">
				<div class="col">
					<h3 class="page-title">Update User</h3>
				</div>
				<div class="col text-right">
                    <a href="{{ route('users.index') }}" class="btn btn-primary">Back</a>
                </div>
			</div>
		</div>
		{{ Form::open(['url' => route('users.update', $user->id), 'id' => 'updateUserForm']) }}
		@method('put')
		<div class="row">
			<div class="col-md-4">
				<div class="profile_user">
					<div class="card">
						<div class="card-body text-center">
							<div class="user-image">                                
                                <img class="rounded-circle img-thumbnail" id="profileImage" src="{{ $user->image }}">
                                <label for="userImg">Upload Picture</label>
                                {{ Form::file('user_image', ['id' => 'userImg', 'style' => 'display:none']) }}
                                {{ Form::hidden('image_name', $user->image, ['id', 'imageName']) }}
                            </div>
						</div>
					</div>
				</div>
			</div>		  
			<div class="col-md-8">				
				<h4 class="card-title">Personal Information</h4>
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-md-6">					
								<div class="form-group">
									{{ Form::label('name', 'Name') }}
                                    {{ Form::text('name', $user->name, ['class' => 'form-control', 'placeholder' => 'Enter Name']) }}										
								</div>
							</div>																			
							<div class="col-md-6">
								<div class="form-group">
									{{ Form::label('email', 'Email') }}
                                    {{ Form::text('email', $user->email, ['class' => 'form-control', 'placeholder' => 'Enter Email', 'readonly' => '']) }}										
								</div>	
							</div>
							<div class="col-md-6">
								<div class="form-group">
									{{ Form::label('gender', 'Gender') }}
                                    {{ Form::select('gender', [
                                    		'' => 'Select',
                                    		'male' => 'Male',
                                    		'female' => 'Female',
                                    		'female' => 'Female',
                                    	], $user->gender, ['class' => 'form-control']) }}
								</div>	
							</div>
							<div class="col-md-6">
								<div class="form-group">
									{{ Form::label('dob', 'Age') }}
                                    {{ Form::text('dob', $user->dob, ['class' => 'form-control', 'placeholder' => 'Enter Age']) }}										
								</div>	
							</div>
											
						</div>								
					</div>
				</div>						
				<div class="card">
					<div class="card-body">
						<h4 class="card-title">Change Password</h4>
						<div class="row">							
							<div class="col-md-6">
								<div class="form-group">
									{{ Form::label('current_password', 'Current Password') }}
									{{ Form::password('current_password', ['class' => 'form-control', 'id' => 'currentPassword', 'autocomplete' => 'new-password']) }}
								</div>	
							</div>
							<div class="col-md-6">
								<div class="form-group">
									{{ Form::label('password', 'Password') }}
									{{ Form::password('password', ['class' => 'form-control', 'id' => 'password', 'autocomplete' => 'new-password']) }}
								</div>	
							</div>
							<div class="col-md-12">
								<div class="form-group">
									{{ Form::label('confirm_password', 'Confirm Password') }}
									{{ Form::password('confirm_password', ['class' => 'form-control', 'id' => 'password']) }}									
								</div>	
							</div>
						</div>
						<div class="text-left">
							{{ Form::button('Update', [
                                'class' => 'btn btn-primary',
                                'id' => 'updateUserFormBtn',
                                'type' => 'Save'
                            ]) }}
						</div>	
					</div>
				</div>												
			</div>
			{{ Form::close() }} 
		</div>
	</div>
</div>
@endsection
@section('customScript')
<script src="{{ asset('public/assets/js/jquery.validate.min.js') }}"></script>
<script type="text/javascript">
	userImg.onchange = evt => {
	  const [file] = userImg.files
	  if (file) {
	    profileImage.src = URL.createObjectURL(file)
	  }
	}	
	$("#updateUserForm").validate({
        rules: {
            name: {
                required: true,
            },
            email: {
                required: true,
            },
            gender: {
                required: true,
            },
            dob: {
                required: true,
            },        
            password: {
                strong_password: function (element) {
                    return $('#currentPassword').is(':filled');
                },
            },               
            confirm_password: {
                required: function (element) {
                    return $('#password').is(':filled');
                },
                equalTo: '[name="password"]'
            },
        },
        messages: {
            name: "Please enter name!",
            email: "Please enter email!",
            dob: "Please enter date of birth!",
            gender: "Please choose gender!",
            confirm_password: {
                required: "Please enter confirm password!",
                equalTo: "Password and confirm password must be matched!",
            },
        },
        submitHandler: function (form) {
            var serializedData = new FormData(form);
            $("#err_mess").html('');
            $('#updateInfluencerDetailFormBtn').attr('disabled', true);
            $('#updateUserFormBtn').html('Processing <i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                headers: {
                    'X-CSRF-Token': $('input[name="_token"]').val()
                },
                type: 'post',
                url: "{{ route('users.update', $user->id) }}",
                data: serializedData,
                dataType: 'json',
                processData: false,
                contentType: false,
                cache: false,
                success: function (data) {
                    if (data.status == true) {
                        $('#updateInfluencerDetailFormBtn').attr('disabled', false);
                        $('#updateUserFormBtn').html('Save');
                       	swal({
						  position: 'top-end',
						  icon: 'success',
						  title: data.message,
						  showConfirmButton: false,
						  timer: 1500
						})
						setTimeout(function(){ 
				           	window.location.href = "{{ route('users.index') }}"; 
				        }, 1500);						
						
                    } else {
                        $('#updateInfluencerDetailFormBtn').attr('disabled', false);
                        $('#updateUserFormBtn').html('Save');
                        swal({
                          icon: 'error',
                          title: 'Oops...',
                          text: data.message,
                        })
                    }
                }
            });
            return false;
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
</script>
@endsection