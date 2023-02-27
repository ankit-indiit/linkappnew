@extends('admin.layout.master')
@section('content')
<div class="page-wrapper">
	<div class="content container-fluid">
		<div class="page-header">
			<div class="row">
				<div class="col">
					<h3 class="page-title">Add Plan</h3>
				</div>
			</div>
		</div>
		{{ Form::open(['url' => route('subscription.store'), 'id' => 'addPlanForm']) }}
		<div class="row">			
			<div class="col-md-12">				
				<h4 class="card-title">Plan Information</h4>
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-md-6">					
								<div class="form-group">
									{{ Form::label('title', 'Title') }}
                                    {{ Form::text('title', '', ['class' => 'form-control', 'placeholder' => 'Enter Title']) }}										
								</div>
							</div>																			
							<div class="col-md-6">
								<div class="form-group">
									{{ Form::label('price', 'Price') }}
                                    {{ Form::text('price', '', ['class' => 'form-control', 'placeholder' => 'Enter Price']) }}										
								</div>	
							</div>
							<div class="col-md-6">
								<div class="form-group">
									{{ Form::label('type', 'Type') }}
                                    {{ Form::select('type', [
                                    		'' => 'Select Type',
                                    		'monthly' => 'monthly',
                                    		'yearly' => 'yearly',
                                    	], '', ['class' => 'form-control']) }}
								</div>	
							</div>
							<div class="col-md-6">
								<div class="form-group">
									{{ Form::label('status', 'Status') }}
                                    {{ Form::select('status', [
                                    		'' => 'Select Type',
                                    		'1' => 'Active',
                                    		'0' => 'In-active',
                                    	], '', ['class' => 'form-control']) }}
								</div>	
							</div>
							<div class="col-md-12">
								<div class="form-group">
									{{ Form::label('description', 'Description') }}
                                    {{ Form::textarea('description', '', ['class' => 'form-control', 'placeholder' => 'Enter Description']) }}
								</div>	
							</div>
							<div class="col-md-12">
								<div class="text-left">
									{{ Form::button('Add', [
		                                'class' => 'btn btn-primary',
		                                'id' => 'addPlanFormBtn',
		                                'type' => 'submit'
		                            ]) }}
								</div>			
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
	$("#addPlanForm").validate({
        rules: {
            title: {
                required: true,
            },
            price: {
                required: true,
            },
            type: {
                required: true,
            },
            status: {
                required: true,
            },            
            description: {
                required: true,
            }
        },
        messages: {
            title: "Please enter title!",
            price: "Please enter price!",
            type: "Please choose type!",
            status: "Please choose status!",                        
            description: "Please enter description!",                        
        },
        submitHandler: function (form) {
            var serializedData = new FormData(form);
            $("#err_mess").html('');
            $('#addPlanFormBtn').attr('disabled', true);
            $('#addPlanFormBtn').html('Processing <i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                headers: {
                    'X-CSRF-Token': $('input[name="_token"]').val()
                },
                type: 'post',
                url: "{{ route('subscription.store') }}",
                data: serializedData,
                dataType: 'json',
                processData: false,
                contentType: false,
                cache: false,
                success: function (data) {
                    if (data.status == true) {
                        $('#addPlanFormBtn').attr('disabled', false);
                        $('#addPlanFormBtn').html('Save');
                       	swal({
						  position: 'top-end',
						  icon: 'success',
						  title: data.message,
						  showConfirmButton: false,
						  timer: 1500
						})
						setTimeout(function(){ 
				           	window.location.href = "{{ route('subscription.index') }}"; 
				        }, 1500);						
						
                    } else {
                        $('#addPlanFormBtn').attr('disabled', false);
                        $('#addPlanFormBtn').html('Save');
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