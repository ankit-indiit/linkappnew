@extends('admin.layout.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col d-flex justify-content-between">
                    <h3 class="page-title">All Plans</h3>
                    <a href="{{ route('subscription.create') }}" class="btn btn-primary">Add Plan</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered subscriptionDatatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Price</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th width="100px">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>          
    </div>
    </div>
@endsection
@section('customScript')
<script type="text/javascript">
    $(function () {
        var table = $('.subscriptionDatatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('subscription.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'id',  sWidth:'5'},
                {data: 'title', name: 'title'},
                {data: 'price', name: 'price'},
                {data: 'type', name: 'type'},
                {data: 'description', name: 'description'},
                {data: 'date', name: 'date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });

    $(document).on('click', '#deletePlan', function(){
      var id = $(this).data('id');
      swal({
          title: "Are you sure?",
           text: "You will not be able to recover this!",
           type: "warning",
           showCancelButton: true,
           confirmButtonColor: "#DD6B55",
           confirmButtonText: "Yes, delete it!",
      }).then((result) => {
          if (result == true) {
            deletePlan(id)
          } else {
              swal("", "Cancelled!", "error", {
                  button: "Close",
            });
          }
      });
    });

    function deletePlan(id) {      
      $.ajax({
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'delete',
        url: "{{ url('admin/subscription') }}/"+id,
        data: { 
         id: id,
        },
        dataType: 'json',
        success: function (data) {
            if (data.status == true) {
               swal("", data.message, "success", {
                  button: "Close",
            });
            $('.swal-button--confirm').on('click', function(){
               window.location.href = "{{ route('subscription.index') }}"; 
            });
            } else {
               swal("", data.message, "error", {
                     button: "Close",
               });
            }
        }
      });
    }
</script>
@endsection