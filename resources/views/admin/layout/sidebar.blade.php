<div class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="{{ route('dashboard') }}">
        <img src="{{ asset('public/img/link-up-logo.png') }}" class="img-fluid" alt="">
        </a>
    </div>
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="{{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-columns"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li class="{{ Route::currentRouteName() == 'users.index' ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}">
                        <i class="fas fa-user"></i> <span>User</span>
                    </a>
                </li>
                <li class="{{ Route::currentRouteName() == 'messages.index' ? 'active' : '' }}">
                    <a href="{{ route('messages.index') }}">
                        <i class="fas fa-comment"></i> <span>Messages</span>
                    </a>
                </li>
                <li class="{{ Route::currentRouteName() == 'contact-us.index' ? 'active' : '' }}">
                    <a href="{{ route('contact-us.index') }}">
                        <i class="fas fa-columns"></i> <span>Contact Us</span>
                    </a>
                </li>
                <li class="{{ Route::currentRouteName() == 'transaction.index' ? 'active' : '' }}">
                    <a href="{{ route('transaction.index') }}">
                        <i class="fas fa-columns"></i> <span>Transaction</span>
                    </a>
                </li>                          
                <li class="{{ Route::currentRouteName() == 'subscription.index' ? 'active' : '' }}">
                    <a href="{{ route('subscription.index') }}">
                        <i class="far fa-calendar-alt"></i> <span>Subscription</span>
                    </a>
                </li>
               {{--  <li class="">
                    <a href="app-setting.php">
                        <i class="fas fa-file-alt"></i> <span>App Setting</span>
                    </a>
                </li> --}}
                <li class="{{ Route::currentRouteName() == 'notification.index' ? 'active' : '' }}">
                    <a href="{{ route('notification.index') }}">
                        <i class="fas fa-bell"></i> <span>Notifications</span>
                    </a>
                </li>
                <li class="submenu">
                    <a href="#" class="">
                        <i class="fas fa-list"></i> 
                        <span> CMS </span> 
                        <span class="menu-arrow pl-4"></span>
                    </a>
                    <ul style="display: none;">
                        <li class="{{ Route::currentRouteName() == 'terms.index' ? 'active' : '' }}">
                            <a href="{{ route('terms.index') }}">
                                <span>Terms & Conditions</span>
                            </a>
                        </li>                        
                        <li>
                            <a href="{{ route('privacy-policy.index') }}">
                                <span>Privacy Policy</span>
                            </a>
                        </li>
                    </ul>
                </li>     
                {{-- <li class="">
                    <a href="{{ route('adminProfile') }}">
                        <i class="fas fa-cog"></i> <span>Profile Setting</span>
                    </a>
                </li> --}}
            </ul>
        </div>
    </div>
</div>