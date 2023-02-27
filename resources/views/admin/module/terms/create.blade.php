@extends('admin.layout.master')
@section('content')
<div class="page-wrapper">
	<div class="content container-fluid">
		<div class="page-header">
			<div class="row">
				<div class="col">
					<h3 class="page-title">Add Term</h3>
				</div>
			</div>
		</div>
		{{ Form::open(['url' => route('terms.store'), 'id' => 'addTermForm']) }}
		<div class="row">			
			<div class="col-md-12">				
				<h4 class="card-title">Terms Information</h4>
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-md-12">					
								<div class="form-group">
									{{ Form::label('title', 'Title') }}
                                    {{ Form::text('title', '', ['class' => 'form-control', 'placeholder' => 'Enter Title']) }}										
								</div>
							</div>																			
							<div class="col-md-12">
								<div class="form-group">
									{{ Form::label('description', 'Description') }}
                                    {{ Form::textarea('description', '', ['class' => 'form-control', 'placeholder' => 'Enter Description']) }}										
								</div>	
							</div>	
							<div class="col-md-12 text-left">
								{{ Form::button('Add', [
	                                'class' => 'btn btn-primary',
	                                'id' => 'addTermFormBtn',
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
	$("#addTermForm").validate({
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
            $('#addTermFormBtn').attr('disabled', true);
            $('#addTermFormBtn').html('Processing <i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                headers: {
                    'X-CSRF-Token': $('input[name="_token"]').val()
                },
                type: 'post',
                url: "{{ route('terms.store') }}",
                data: serializedData,
                dataType: 'json',
                processData: false,
                contentType: false,
                cache: false,
                success: function (data) {
                    if (data.status == true) {
                        $('#addTermFormBtn').attr('disabled', false);
                        $('#addTermFormBtn').html('Save');
                       	swal({
						  position: 'top-end',
						  icon: 'success',
						  title: data.message,
						  showConfirmButton: false,
						  timer: 1500
						})
						setTimeout(function(){ 
				           	window.location.href = "{{ route('terms.index') }}"; 
				        }, 1500);						
						
                    } else {
                        $('#addTermFormBtn').attr('disabled', false);
                        $('#addTermFormBtn').html('Save');
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