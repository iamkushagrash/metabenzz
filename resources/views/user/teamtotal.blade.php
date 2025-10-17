@extends('user.layouts.app')

@section('title', 'Total Team')

@section('content')
<div class="container-fluid content-inner mt-4 py-4">
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="/User/Dashboard">DASHBOARD</a></li>
        <li class="breadcrumb-item active">Total Team</li>
    </ul>
    
    <h1 class="page-header">Total Team</h1>
    <hr class="mb-4">

    <!-- Total Team Table -->
    <div class="card mb-5">
        <div class="card-body">
            <p>Here is your Total Team. Status indicates whether the member is active or inactive.</p>
            <div class="table-responsive">
                <table id="teamTotalTable" class="table text-nowrap w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Registration Date</th>
                            <th>Level</th>
                            <th>Package($)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach($totaldown as $member)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $member->userid }}</td>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->doj }}</td>
                            <td>{{ $member->level }}</td>
                            <td>{{ round($member->current, 2) }}</td>
                            <td><span style="color:#{{ $member->statusclass }}">{{ $member->status }}</span></td>
                        </tr>
                        @php $i++; @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Registration Date</th>
                            <th>Level</th>
                            <th>Package($)</th>
                            <th>Status</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-arrow">
            <div class="card-arrow-top-left"></div>
            <div class="card-arrow-top-right"></div>
            <div class="card-arrow-bottom-left"></div>
            <div class="card-arrow-bottom-right"></div>
        </div>
    </div>
</div>
<!-- Add this in your <head> or before your custom script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    
$(document).ready(function() {
    $('#teamTotalTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'asc']]
    });
});
</script>
@endsection
