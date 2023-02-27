@extends('admin.layout.master')
@section('content')
<style type="text/css">
	.ck-blurred.ck.ck-content.ck-editor__editable.ck-rounded-corners.ck-editor__editable_inline {
		height: 500px;
	}
</style>
<div class="page-wrapper">
	<div class="content container-fluid">
		<div class="page-header">
			<div class="row">
				<div class="col">
					<h3 class="page-title">Update Privacy Policy</h3>
				</div>
			</div>
		</div>
		{{ Form::open(['url' => route('privacy-policy.update', $privacyPolicy->id), 'id' => 'updateTermForm']) }}
		@method('put')
		<div class="row">			
			<div class="col-md-12">				
				<h4 class="card-title">Terms Privacy Policy</h4>
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-md-12">					
								<div class="form-group">
									{{ Form::label('title', 'Title') }}
                                    {{ Form::text('title', @$privacyPolicy->title, ['class' => 'form-control', 'placeholder' => 'Enter Title']) }}										
								</div>
							</div>																			
							<div class="col-md-12">
								<div class="form-group">
									{{ Form::label('description', 'Description') }}
                                    {{ Form::textarea('description', @$privacyPolicy->description, ['class' => 'form-control', 'id' => 'description', 'rows' => 5, 'placeholder' => 'Enter Description']) }}										
								</div>	
							</div>	
							<div class="col-md-12 text-left">
								{{ Form::button('Update', [
	                                'class' => 'btn btn-primary',
	                                'id' => 'updateTermFormBtn',
	                                'type' => 'submit'
	                            ]) }}
							</div>				
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
	ClassicEditor.create(document.querySelector('#description')).catch( error => {
		console.error( error );
	});
	$("#updateTermForm").validate({
        rules: {
            title: {
                required: true,
            },
            description: {
                required: true,
            },
        },
        messages: {
            title: "Please enter title!",
            description: "Please enter description!",
        },
        submitHandler: function (form) {
            var serializedData = new FormData(form);
            $("#err_mess").html('');
            $('#updateTermFormBtn').attr('disabled', true);
            $('#updateTermFormBtn').html('Processing <i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                headers: {
                    'X-CSRF-Token': $('input[name="_token"]').val()
                },
                type: 'post',
                url: "{{ route('privacy-policy.update', $privacyPolicy->id) }}",
                data: serializedData,
                dataType: 'json',
                processData: false,
                contentType: false,
                cache: false,
                success: function (data) {
                    if (data.status == true) {
                        $('#updateTermFormBtn').attr('disabled', false);
                        $('#updateTermFormBtn').html('Save');
                       	swal({
						  position: 'top-end',
						  icon: 'success',
						  title: data.message,
						  showConfirmButton: false,
						  timer: 1500
						})
						setTimeout(function(){ 
				           	window.location.href = "{{ route('privacy-policy.index') }}"; 
				        }, 1500);						
						
                    } else {
                        $('#updateTermFormBtn').attr('disabled', false);
                        $('#updateTermFormBtn').html('Save');
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
</script>
@endsection