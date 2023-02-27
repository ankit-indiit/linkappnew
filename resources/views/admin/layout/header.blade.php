<div class="header">
    <div class="header-left">
        <a href="{{ route('dashboard') }}" class="logo logo-small">
        <img src="{{ asset('public/assets/img/logo-icon.png') }}" alt="Logo" width="30" height="30">
        </a>
    </div>
    <a href="javascript:void(0);" id="toggle_btn">
    <i class="fas fa-align-left"></i>
    </a>
    <a class="mobile_btn" id="mobile_btn" href="javascript:void(0);">
    <i class="fas fa-align-left"></i>
    </a>
    <ul class="nav user-menu">
        <li class="nav-item dropdown noti-dropdown">
            <a href="javascript:void(0)" class="dropdown-toggle nav-link" data-toggle="dropdown">
            <i class="far fa-bell"></i> <span class="badge badge-pill"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right notifications">
                <div class="topnav-dropdown-header">
                    <span class="notification-title">Notifications</span>
                    <a href="javascript:void(0)" class="clear-noti"> Clear All </a>
                </div>
                <div class="noti-content">
                    <ul class="notification-list">
                        @if (count(getNotifications()) > 0)
                            @foreach (getNotifications() as $notification)
                                <li class="notification-message">
                                    <a href="javascript:void(0)">
                                        <div class="media">
                                            <span class="avatar avatar-sm">
                                            <img class="avatar-img rounded-circle" alt="" src="{{ getUserImageById($notification->from_id) }}">
                                            </span>
                                            <div class="media-body">
                                                <p class="noti-details">
                                                    @switch($notification->text)
                                                        @case('match')
                                                            <span class="noti-title">{{ getUserNameById($notification->to_id).' has found a new match with '.getUserNameById($notification->from_id) }}</span>
                                                        @break
                                                        @case('register')
                                                            <span class="noti-title">New user {{ getUserNameById($notification->from_id).' has registered on the App' }}</span>
                                                        @break
                                                    @endswitch                                                                                   
                                                </p>
                                                <p class="noti-time">
                                                    <span class="notification-time">{{ date('d F Y', strtotime($notification->created_at)) }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        @else
                            <li class="text-center">No Notification Found!</li>
                        @endif                        
                    </ul>
                </div>
                <div class="topnav-dropdown-footer">
                    <a href="{{ route('notification.index') }}">View all Notifications</a>
                </div>
            </div>
        </li>
        <li class="nav-item dropdown">
            <a href="javascript:void(0)" class="dropdown-toggle user-link  nav-link" data-toggle="dropdown">
            <span class="user-img">
            <img class="rounded-circle" src="{{ Auth::guard('admin')->user()->image }}" width="40" alt="Admin">
            </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="{{ route('adminProfile', Auth::guard('admin')->user()->id) }}">Profile</a>
                <form action="{{ route('logOut') }}" method="post">
                    @csrf
                    <button type="submit" class="dropdown-item">Logout</button>                    
                </form>
            </div>
        </li>
    </ul>
</div>