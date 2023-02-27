@extends('admin.layout.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">All Messages</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered user_datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User1</th>
                                        <th>User2</th>
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
        var table = $('.user_datatable').DataTable({
            processing: true,
            serverSide: true,
            order: [[3, 'desc']],
            ajax: "{{ route('messages.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'id',  sWidth:'5'},
                {data: 'from', name: 'from'},
                {data: 'to', name: 'to'},
                {data: 'date', name: 'date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });

    $(document).on('click', '#deleteUser', function(){
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
            deleteUser(id)
          } else {
              swal("", "Cancelled!", "error", {
                  button: "Close",
            });
          }
      });
    });

    function deleteUser(id) {      
      $.ajax({
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'delete',
        url: "{{ url('admin/users') }}/"+id,
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
               window.location.href = "{{ route('users.index') }}"; 
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