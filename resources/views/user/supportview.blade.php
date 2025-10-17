@extends('user.layouts.app')

@section('title', 'Support')

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/User/Dashboard') }}">DASHBOARD</a></li>
            <li class="breadcrumb-item active" aria-current="page">SUPPORT</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <h1 class="page-header mb-4">Support</h1>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Alert!</strong> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tickets Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Your Tickets</h4>
                    <!-- Button on right corner -->
                    <a href="#" class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#createTicketModal">
                        <i class="btn-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 me-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </i>
                        <span>Create Ticket</span>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatableDefault" class="table table-striped table-hover text-nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1; @endphp
                                @foreach($ticket as $t)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $t->sub }}</td>
                                    <td>{{ $t->title }}</td>
                                    <td>{{ $t->status }}</td>
                                    
                                    <td>{{ $t->created_at }}</td>
                                    <td>
                                        <a href="{{ url('/User/TicketView/'.str_replace(' ', '-', $t->title).'/'.$t->subid) }}" class="btn btn-primary btn-icon">View</a>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <a href="#" data-bs-toggle="scroll-to-top" class="btn-scroll-top fade">
        <i class="fa fa-arrow-up"></i>
    </a>

</div>

<!-- Create Ticket Modal -->
<div class="modal fade" id="createTicketModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createTicketLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ url('/User/CreateTicket') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="createTicketLabel">Create New Ticket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="subject" class="form-label">Select Topic</label>
                        <select id="subject" name="subject" class="form-select @error('subject') is-invalid @enderror" required>
                            <option value="">Select a Topic</option>
                            <option value="Profile Edit">Profile Edit</option>
                            <option value="Deposit">Deposit</option>
                            <option value="Withdraw Related">Withdraw Related</option>
                            <option value="Others">Others</option>
                        </select>
                        @error('subject')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="Ticket Title" required>
                        @error('title')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea id="message" name="message" class="form-control @error('message') is-invalid @enderror" placeholder="Your message..." rows="5" required></textarea>
                        @error('message')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Ticket</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
