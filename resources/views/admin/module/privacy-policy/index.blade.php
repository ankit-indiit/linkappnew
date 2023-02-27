@extends('admin.layout.master')
@section('content')
<style>
    .termDatatable tbody tr td:nth-child(3) {
      width: 300;
      max-width: 300px;
      word-break: break-all;
      white-space: pre-line;
    }
    .termDatatable tbody tr td:nth-child(2) {
      width: 200;
      max-width: 200px;
      word-break: break-all;
      white-space: pre-line;
    }
</style>
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col d-flex justify-content-between">
                    <h3 class="page-title">Privacy Policy</h3>
                    <a href="{{ route('privacy-policy.create') }}" class="btn btn-primary">Add</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered termDatatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Description</th>
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
        var table = $('.termDatatable').DataTable({
            processing: true,
            serverSide: true,
            order: [[ 0, "desc" ]],
            ajax: "{{ route('privacy-policy.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'title', name: 'title'},
                {data: 'description', name: 'description'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });

    $(document).on('click', '#deletePrivacyPolicy', function(){
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
            deletePrivacyPolicy(id)
          } else {
              swal("", "Cancelled!", "error", {
                  button: "Close",
            });
          }
      });
    });

    function deletePrivacyPolicy(id) {      
      $.ajax({
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'delete',
        url: "{{ url('admin/privacy-policy') }}/"+id,
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
               window.location.href = "{{ route('privacy-policy.index') }}"; 
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