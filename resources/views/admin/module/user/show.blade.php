@extends('admin.layout.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col d-flex justify-content-between">
                    <h3 class="page-title">User Detail</h3>
                    <a href="{{ route('users.index') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <img src="{{ $user->image }}" width="50" height="50">
                            </div>
                            <div class="col-md-5">
                                <b>Name:- </b> {{ $user->name }}<br>
                                <b>Email:- </b> {{ $user->email }}
                            </div>
                            <div class="col-md-5">
                                <b>DOB:- </b> {{ $user->dob }}<br>
                                <b>Gender:- </b> {{ $user->gender }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered user_datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        {{-- <th>User</th> --}}
                                        <th>Links</th>
                                        <th>Date</th>
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
    @php
        $userId = Request::segment(3);
    @endphp
    </div>
@endsection
@section('customScript')
<script type="text/javascript">
    $(function () {
        var table = $('.user_datatable').DataTable({
            processing: true,
            serverSide: true,
            order: [[ 0, "desc" ]],
            ajax: "{{ route('users.show', $userId) }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', sWidth:'5'},
                // {data: 'to_id', name: 'to_id'},
                {data: 'matches', name: 'matches'},
                {data: 'date', name: 'date'},
            ]
        });
    });
</script>
@endsection