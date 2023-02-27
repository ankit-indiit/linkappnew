@extends('admin.layout.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col d-flex justify-content-between">
                    <h3 class="page-title">Contact Us List</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered contactUsDatatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Message</th>
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
        var table = $('.contactUsDatatable').DataTable({
            processing: true,
            serverSide: true,
            order: [[ 0, "desc" ]],
            ajax: "{{ route('contact-us.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'message', name: 'message'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });

    $(document).on('click', '#deleteContactUs', function(){
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
            deleteContactUs(id)
          } else {
              swal("", "Cancelled!", "error", {
                  button: "Close",
            });
          }
      });
    });

    function deleteContactUs(id) {      
      $.ajax({
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'delete',
        url: "{{ url('admin/contact-us') }}/"+id,
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
               window.location.href = "{{ route('contact-us.index') }}"; 
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