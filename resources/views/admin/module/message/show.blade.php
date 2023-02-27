@extends('admin.layout.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Chat</h3>
                </div>
                <div class="col text-right">
                    <a href="{{ route('messages.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <ul class="conversation-list slimscroll" style="max-height: 100vh">
                        	@foreach ($messages as $message)
                                @php
                                    $index = $loop->iteration;
                                @endphp
                                @if ($toId == $message->from_id)
                            		<li class="clearfix">
                                        <div class="chat-avatar groupchatimg">
                                            <img src="{{ $message->to_image }}" class="rounded" alt="">
                                            {{-- <i>{{ $message->time }}</i> --}}
                                        </div>
                                        <div class="conversation-text">
                                            <div class="ctext-wrap">
                                                <i>{{ $message->to }}</i>
                                                <p>{{ $message->message }}</p>
                                            </div>
                                        </div>
                                    </li>
                                @else
                                    <li class="clearfix odd">
                                        <div class="chat-avatar">
                                            <img src="{{ $message->from_image }}" class="rounded" alt="">
                                            {{-- <i>{{ @$message->time }}</i> --}}
                                        </div>
                                        <div class="conversation-text">
                                            <div class="ctext-wrap">
                                                <i>{{ $message->from }}</i>
                                                <p>{{ $message->message }}</p>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            @endforeach
                        </ul>                            
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection