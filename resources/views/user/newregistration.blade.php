@extends('user.layouts.app')

@section('title', 'Register')

@section('content')
<div class="container-fluid content-inner mt-4 py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header position-relative">
                    <h4 class="card-title mb-0 text-center w-100">New Registration</h4>
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

                    <!-- Registration Form -->
                    @if(!session('success'))
                    <form method="POST" action="/User/NewRegistration" id="registrationForm">
                        @csrf
                        <div class="row g-4">

                            <!-- Sponsor ID -->
                            <div class="col-md-6">
                                <label for="referrer" class="form-label">Sponsor ID</label>
                                <input type="text" name="referrer" id="referrer" class="form-control @error('referrer') is-invalid @enderror" placeholder="Sponsor ID" required>
                                @error('referrer')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            <!-- Sponsor Name -->
                            <div class="col-md-6">
                                <label for="spname" class="form-label">Sponsor Name</label>
                                <input type="text" name="referrername" id="spname" class="form-control @error('referrername') is-invalid @enderror" placeholder="Sponsor Name" disabled>
                                @error('referrername')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            <!-- Full Name -->
                            <div class="col-md-12">
                                <label for="exampleFormControlInput4" class="form-label">Full Name</label>
                                <input type="text" name="name" id="exampleFormControlInput4" class="form-control @error('name') is-invalid @enderror" placeholder="Full Name" required>
                                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            <!-- Email Address -->
                            <div class="col-md-12">
                                <label for="exampleFormControlInput5" class="form-label">Email Address</label>
                                <input type="email" name="email" id="exampleFormControlInput5" class="form-control @error('email') is-invalid @enderror" placeholder="Email Address" required>
                                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            <!-- Mobile Number with Country Code -->
                            <div class="col-md-12">
                                <label class="form-label">Mobile Number</label>
                                <div class="input-group">
                                    <select name="countrycode" class="form-select col-md-3">
                                         <option data-countryCode="IN" value="91">India (+91)</option>
                                        <option data-countryCode="SG" value="65">Singapore (+65)</option>
                                        <option data-countryCode="US" value="1">USA (+1)</option>
                                        <option data-countryCode="GB" value="44">UK (+44)</option>
                                        <!-- Add more options as needed -->
                                    </select>
                                    <input type="text" name="contact" id="exampleFormControlInput6" class="form-control @error('contact') is-invalid @enderror" placeholder="Mobile No" maxlength="11">
                                    @error('contact')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6">
                                <label for="exampleFormControlInput8" class="form-label">Password</label>
                                <input type="password" name="password" id="exampleFormControlInput8" class="form-control @error('password') is-invalid @enderror" placeholder="New Password" required>
                                @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6">
                                <label for="password-confirm" class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password-confirm" class="form-control" placeholder="Repeat Password" required>
                            </div>

                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary px-5" id="submitBtn">Register</button>
                        </div>
                    </form>
                    @endif

                    <!-- Success Form -->
                    @if(session('success'))
                    <div class="text-center">
                        <h3 class="mb-3">Registration Successful</h3>
                        <p><strong>User ID:</strong> {{ session('details.uniqueid') }}</p>
                        <p><strong>Email:</strong> {{ session('details.email') }}</p>
                        <p><strong>Password:</strong> {{ session('details.password') }}</p>
                        <a href="/User/NewRegistration" class="btn btn-outline-primary mt-3">Back</a>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function(){

    // Disable submit button on form submit
    $('#registrationForm').on('submit', function(){
        $('#submitBtn').prop('disabled', true).text('Processing, please wait...');
    });

    // Sponsor ID AJAX check
    $("#referrer").on('blur', function(){
        let sponsorId = $(this).val();
        if(sponsorId === "") return;

        $.ajax({
            type:'GET',
            url:'/getSponsorNew/' + sponsorId,
            dataType: "json",
            success:function(data){
                if(data.status == 0){
                    $("#spname").val(data.name);
                } else {
                    $("#spname").val('');
                }
            }
        });
    });
});
</script>
@endsection
