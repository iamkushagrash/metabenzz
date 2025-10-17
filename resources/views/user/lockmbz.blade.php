@extends('user.layouts.app')

@section('title', 'Locking mbz')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header text-center">
                    <h4 class="card-title mb-0">Locking mbz</h4>
                </div>
                <div class="card-body">

                    <!-- Messages -->
                    @foreach (['success','warning'] as $msg)
                        @if(session($msg))
                            <div class="alert alert-{{ $msg }} alert-dismissible fade show">
                                <strong>{{ ucfirst($msg) }}!</strong> {{ session($msg) }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                    @endforeach

                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            <div class="alert alert-danger alert-dismissible fade show">
                                <strong>Alert!</strong> {{ $error }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endforeach
                    @endif

                    <!-- Locking Form -->
                    <form action="/User/Lock" method="post" id="lockForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Available Balance ($)</label>
                            <input type="text" class="form-control" value="@if(!is_null($balance)){{round(\Illuminate\Support\Facades\Crypt::decrypt($balance->amount),6)}}@else 0 @endif" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">MBZ Current Price ($)</label>
                            <input type="text" class="form-control" value="{{round($price->price,3)}}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount ($)</label>
                            <input type="number" id="amount" name="amount" class="form-control" placeholder="Amount in USD">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter your password">
                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary" onclick="this.disabled=true;this.form.submit();">Lock Now</button>
                            <a href="/User/Lock" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    // Optional: convert between USD and MBZ if needed
    $("#amount").on('blur', function(){
        $("#mbz").val((parseFloat($("#amount").val()) / {{$price->price}}).toFixed(6));
    });
</script>
@endsection
