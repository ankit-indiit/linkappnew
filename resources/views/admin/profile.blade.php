@extends('admin.layout.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Profile Setting</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="profile_user">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="user-image">
                                {{ Form::open(['url' => route('updateProfileImage'), 'id' => 'updateProfileImageForm']) }}
                                    <img class="rounded-circle img-thumbnail" id="adminProfileImage" src="{{ @$admin->image }}">
                                    <label for="user-img">Upload Picture</label>
                                    {{ Form::file('image', ['id' => 'user-img', 'style' => 'display:none']) }}
                                    {{ Form::hidden('image_name', @$admin->image, ['id', 'imageName']) }}
                                    {{ Form::hidden('id', @$admin->id) }}
                                {{ Form::close() }}       
                            </div>
                        </div>
                    </div>
                </div>
            </div>        
            <div class="col-md-8">
                {{ Form::open(['url' => route('updateProfile'), 'id' => 'updateProfileForm']) }}
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Personal Information</h4>
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="form-group row">
                                    {{ Form::label('name', 'Name', ['class' => 'col-lg-12 col-form-label']) }}
                                    <div class="col-lg-12">
                                        {{ Form::text('name', @$admin->name, ['class' => 'form-control', 'placeholder' => 'Enter Name']) }}                                    
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="form-group row">
                                    {{ Form::label('email', 'Email Address', [
                                        'class' => 'col-lg-12 col-form-label'
                                    ]) }}
                                    <div class="col-lg-12">
                                        {{ Form::text('email', @$admin->email, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter Email',
                                        ]) }}                                    
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="form-group row">
                                    {{ Form::label('phone', 'Phone Number', [
                                        'class' => 'col-lg-12 col-form-label'
                                    ]) }}
                                    <div class="col-lg-12">
                                        {{ Form::text('phone', @$admin->phone, ['class' => 'form-control', 'placeholder' => 'Enter Phone Number']) }}                                    
                                    </div>
                                </div>
                            </div>                                          
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Change Password</h4>
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="form-group row">
                                    {{ Form::label('current_password', 'Current Password', [
                                        'class' => 'col-lg-12 col-form-label'
                                    ]) }}
                                    <div class="col-lg-12">
                                        {{ Form::password('current_password', [
                                            'class' => 'form-control',
                                            'autocomplete' => 'new-password',
                                            'id' => 'current_password'
                                        ]) }}                                    
                                    </div>
                                </div>
                            </div>   
                            <div class="col-xl-6">
                                <div class="form-group row">
                                    {{ Form::label('new_password', 'New Password', [
                                        'class' => 'col-lg-12 col-form-label',
                                        'id' => 'new_password'
                                    ]) }}
                                    <div class="col-lg-12">
                                        {{ Form::password('new_password', ['class' => 'form-control', 'id' => 'new_password']) }}                                    
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="form-group row">
                                    {{ Form::label('confirm_password', 'Confirm New Password', [
                                        'class' => 'col-lg-12 col-form-label'
                                    ]) }}
                                    <div class="col-lg-12">
                                        {{ Form::password('confirm_password', ['class' => 'form-control']) }}                                    
                                    </div>
                                </div>
                            </div>                                                                
                        </div>
                        {{ Form::hidden('id', @$admin->id) }}
                        <div class="text-right">
                            {{ Form::button('Save', [
                                'class' => 'btn btn-primary',
                                'id' => 'updateProfileFormBtn',
                                'type' => 'Save'
                            ]) }}
                        </div>
                    </div>
                </div>
                {{ Form::close() }}      
            </div>
        </div>
    </div>
</div>
@endsection
@section('customScript')
<script src="{{ asset('public/assets/js/jquery.validate.min.js') }}"></script>
<script type="text/javascript">
    $('#user-img').change(function(e) {
      var form = $('#updateProfileImageForm')[0];
      var serializedData = new FormData(form);
      $.ajax({
         headers: {
            'X-CSRF-Token': $('input[name="_token"]').val()
         },
         type: 'post',
         url: "{{ route('updateProfileImage') }}",
         data: serializedData,
         dataType: 'json',
         processData: false,
         contentType: false,
         cache: false,   
         success: function(data) {
            if (data.status == true) {
                $('#adminProfileImage').attr('src', data.image);
                $('#imageName').val(data.image_name);
                swal({
                  position: 'top-end',
                  icon: 'success',
                  title: data.message,
                  showConfirmButton: false,
                })
            } else {
                swal({
                  icon: 'error',
                  title: 'Oops...',
                  text: data.message,
                })
            }        
         }
      });
    });

    $("#updateProfileForm").validate({
        rules: {
            name: {
                required: true,
            },
            email: {
                required: true,
            },
            phone: {
                required: true,
            },
            image: {
                required: true,
            },
            new_password: {
                required: true,
                strong_password: true,
            },
            confirm_password: {
                required: function (element) {
                    return $('#new_password').is(':filled');
                },
                equalTo: '[name="new_password"]'
            },
        },
        messages: {
            name: "Please enter name!",
            email: "Please enter email!",
            phone: "Please enter phone!",
            phone: "Please enter phone!",
            image: "Please enter image!",
            new_password: {
                required: "Please enter password!",
                minlength: "Length must be at least 8 characters!",
            },
            confirm_password: {
                required: "Please enter confirm password!",
                equalTo: "Password and confirm password must be matched!",
            },          
        },
        submitHandler: function (form) {
            var serializedData = new FormData(form);
            $("#err_mess").html('');
            $('#updateInfluencerDetailFormBtn').attr('disabled', true);
            $('#updateProfileFormBtn').html('Processing <i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                headers: {
                    'X-CSRF-Token': $('input[name="_token"]').val()
                },
                type: 'post',
                url: "{{ route('updateProfile') }}",
                data: serializedData,
                dataType: 'json',
                processData: false,
                contentType: false,
                cache: false,
                success: function (data) {
                    if (data.status == true) {
                        $('#updateInfluencerDetailFormBtn').attr('disabled', false);
                        $('#updateProfileFormBtn').html('Save');
                        swal({
                          position: 'top-end',
                          icon: 'success',
                          title: data.message,
                          showConfirmButton: false,
                        })
                    } else {
                        $('#updateInfluencerDetailFormBtn').attr('disabled', false);
                        $('#updateProfileFormBtn').html('Save');
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