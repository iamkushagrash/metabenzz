@extends('user.layouts.app')

@section('title', 'View Ticket')

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">

   
  

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Alert!</strong> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                      <!-- Page Header -->
    <h6 class="page-header mb-4">
        View Ticket <small>({{ $viewticket[0]->title }})</small>
    </h6>

                    <!-- Chat Messages -->
                    <div class="widget-chat mb-4">
                        @foreach ($viewticket as $view)
                            @if($view->ustatus == 0)
                                <!-- User Reply -->
                                <div class="widget-chat-item reply mb-3">
                                    <div class="widget-chat-content">
                                        <div class="widget-chat-message last p-2 bg-light rounded">
                                            {!! $view->htext !!}
                                        </div>
                                        <div class="widget-chat-status text-muted small mt-1">{{ $view->created_at }}</div>
                                    </div>
                                </div>
                            @else
                                <!-- Support Reply -->
                                <div class="widget-chat-item mb-3">
                                    <div class="widget-chat-content">
                                        <div class="widget-chat-name fw-bold">Support</div>
                                        <div class="widget-chat-message last p-2 bg-primary text-white rounded">
                                            {!! $view->htext !!}
                                        </div>
                                        <div class="widget-chat-status text-muted small mt-1">{{ $view->created_at }}</div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Reply Form -->
                    <form action="{{ url('/User/ReplyTicket') }}" method="post">
                        @csrf
                        <input type="hidden" name="ticket" value="{{ $viewticket[0]->subid }}">
                        <div class="mb-3">
                            <label for="replyText" class="form-label">Add Reply</label>
                            <textarea name="textmsg" id="replyText" rows="4" class="form-control @error('textmsg') is-invalid @enderror" placeholder="Type your message..." required></textarea>
                            @error('textmsg')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Reply</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection
