@extends('user.layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="container-fluid content-inner mt-4 py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header text-center">
                    <h4 class="card-title mb-0">Change Password</h4>
                </div>

                <div class="card-body">

                    <!-- Messages -->
                    @foreach (['success','warning'] as $msg)
                        @if(session($msg))
                            <div class="alert alert-{{ $msg == 'warning' ? 'danger' : $msg }} alert-dismissible fade show">
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

                    <!-- Change Password Form -->
                    <form action="/User/ChangePassword" method="post">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label class="form-label" for="oldpassword">Old Password</label>
                                    <input type="password" class="form-control @error('oldpassword') is-invalid @enderror" name="oldpassword" placeholder="Old Password *" required>
                                    @error('oldpassword')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label" for="password">New Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="New Password *" required>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label" for="password_confirmation">Confirm Password</label>
                                    <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password *" required>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success px-5" onclick="this.disabled=true;this.innerText='Processing, please wait...';this.form.submit();">
                                Submit
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
