@extends('admin.layout.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col d-flex justify-content-between">
                    <h3 class="page-title">All Notifications</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                      <div id="plist" class="people-list admin-notifications-lists">
                          <ul class="list-unstyled chat-list mt-2 mb-0">
                                @if (count($notifications) > 0)
                                    @foreach ($notifications as $notification)
                                        <li class="clearfix"> 
                                            <img src="{{ getUserImageById($notification->from_id) }}" alt="avatar">
                                            @switch($notification->text)
                                                @case('match')
                                                    <div class="about">
                                                        <div class="ntfc-title">New Match Found</div>
                                                        <div class="ntfc-message"><strong>{{getUserNameById($notification->to_id)}}</strong> has found a new match with <strong>{{getUserNameById($notification->from_id)}}</strong></div>
                                                    </div>
                                                @break  
                                                @case('register')
                                                    <div class="about">
                                                        <div class="ntfc-title">New User Registered</div>
                                                        <div class="ntfc-message">New user <strong>{{getUserNameById($notification->from_id)}}</strong> has Registered on the App</div>
                                                    </div>
                                                @break                                               
                                                @default                                                    
                                            @endswitch                                           
                                           <div class="date-time text-right">
                                                <div class="time"><i class="fa fa-clock" aria-hidden="true"></i> {{\Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                                </div>
                                           </div>
                                        </li>
                                    @endforeach
                                    <div class="d-flex justify-content-end">
                                        {{ $notifications->links() }}                                        
                                    </div>
                                @else
                                    <li>No Notification Found!</li>
                                @endif
                            </ul>
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
        var table = $('.notificationDatatable').DataTable({
            processing: true,
            serverSide: true,
            order: [[ 3, "desc" ]],
            ajax: "{{ route('notification.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'id',  sWidth:'5'},
                {data: 'from', name: 'from'},
                {data: 'to', name: 'to'},
                {data: 'message', name: 'message'},               
                {data: 'date', name: 'date'},
            ]
        });
    });
</script>
@endsection