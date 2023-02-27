@extends('admin.layout.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-12">
                    <h3 class="page-title">Welcome Link</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-primary">
                            <i class="fas fa-hand-holding-usd"></i>
                            </span>
                            <div class="dash-widget-info">
                                <h3>${{ $totalPayments }}</h3>
                                <h6 class="text-muted">Revenue</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-info">
                            <i class="fas fa-users"></i>
                            </span>
                            <div class="dash-widget-info">
                                <h3>{{ $totalUsers }}</h3>
                                <h6 class="text-muted">Total Users</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-success">
                            <i class="fas fa-users"></i>
                            </span>
                            <div class="dash-widget-info">
                                <h3>{{ $totalMatch }}</h3>
                                <h6 class="text-muted">Links</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon">
                            <i class="fas fa-comments"></i>
                            </span>
                            <div class="dash-widget-info">
                                <h3>{{ $chatCount }}</h3>
                                <h6 class="text-muted">Chats</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">  
                        <div class="d-flex justify-content-between">
                            <h4 class="mb-4">Recent Users</h4>                         
                            <a href="{{ route('users.index') }}">View all</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Image</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($recentUsers) > 0)
                                        @foreach ($recentUsers as $user)
                                            <tr>
                                                <td>{{ $loop->iteration  }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    <a href="javascript:void(0)" class="avatar avatar-md">
                                                        <img class="avatar-img rounded-circle" src="{{ $user->image }}">
                                                    </a>
                                                </td>
                                                <td class="text-nowrap"><a href="{{ route('users.show', $user->id) }}" class="btn bg-primary-light"><i class="fas fa-eye"></i> View</a><a href="{{ route('users.edit', $user->id) }}" class="btn bg-warning-light "><i class="fas fa-edit"></i> Edit</a><a href="javascript:void(0)" class="btn btn-sm bg-danger-light" id="deleteUser" data-id="{{ $user->id }}"><i class="fas fa-trash-alt"></i> Delete</a></td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="text-center">No User Found!</tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>                        
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body"> 
                        <div class="d-flex justify-content-between">
                            <h4 class="mb-4">Recent Transactions</h4>                            
                            <a href="{{ route('transaction.index') }}">View all</a>
                        </div>                                            
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Plan</th>
                                        <th>Transaction ID</th>
                                        <th>Transaction Method</th>
                                        <th>Amount</th>
                                        <th>Date/Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($recentTransactions) > 0)
                                        @foreach ($recentTransactions as $transaction)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ getUserNameById($transaction->user_id) }}</td>
                                                <td>Basic</td>
                                                <td>{{ $transaction->subscription_id }}</td>
                                                <td>{{ $transaction->payment_type }}</td>
                                                <td>${{ $transaction->amount }}</td>
                                                <td class="text-nowrap">{{ $transaction->created_at }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="text-center">No Transaction Found!</tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection