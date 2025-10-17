@extends('user.layouts.app')

@section('title', 'Stake mbz')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

 <div class="conatiner-fluid content-inner mt-n5 py-0">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header text-center">
                    <h4 class="card-title mb-0">Stake mbz</h4>
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

                    <!-- Stake Form -->
                    @if(is_null($user))
                        <form action="/User/getUser" method="post">
                            @csrf
                            <div class="mb-3">
                                <label for="userid" class="form-label">Enter User ID</label>
                                <input type="text" id="userid" name="userid" class="form-control" placeholder="Enter UserID" required>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Search User</button>
                            </div>
                        </form>
                    @else
                        <form action="/User/Drag" method="post" id="stakeForm">
                            @csrf
                            <input type="hidden" name="honeypotu" value="{{$user->id}}">
                            <div class="mb-3">
                                <label class="form-label">User ID</label>
                                <input type="text" class="form-control" value="{{ $user->uuid }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label class="form-label">Available Balance ($)</label>
                                    <input type="text" class="form-control" value="@if(!is_null($balance)){{round(\Illuminate\Support\Facades\Crypt::decrypt($balance->amount),6)}}@else 0 @endif" readonly>
                                </div>
                                <div class="col">
                                    <label class="form-label">mbz Price ($)</label>
                                    <input type="text" class="form-control" value="{{round($price->price,3)}}" readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">mbz Amount</label>
                                <input type="number" id="mbz" name="mbz" class="form-control" placeholder="Enter mbz">
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
                                <button type="submit" class="btn btn-primary" onclick="this.disabled=true;this.form.submit();">Stake Now</button>
                                <a href="/User/Drag" class="btn btn-outline-secondary ms-2">Cancel</a>
                            </div>
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $("#mbz").on('blur', function(){
        $("#amount").val((parseFloat($("#mbz").val()) * {{$price->price}}).toFixed(6));
    });
    $("#amount").on('blur', function(){
        $("#mbz").val((parseFloat($("#amount").val()) / {{$price->price}}).toFixed(6));
    });
</script>
@endsection
