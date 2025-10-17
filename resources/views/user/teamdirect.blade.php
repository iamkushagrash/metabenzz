@extends('user.layouts.app')

@section('title', 'Direct Team')

@section('content')
<div class="container-fluid content-inner mt-4 py-4">
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="/User/Dashboard">DASHBOARD</a></li>
        <li class="breadcrumb-item active">DIRECT TEAM</li>
    </ul>
    
    <h1 class="page-header">Direct Team</h1>
    <hr class="mb-4">

    <div class="card mb-5">
        <div class="card-body">
            <p>Here is your direct team. Status indicates whether the member is active or inactive.</p>
            <div class="table-responsive">
                <table id="directTeamTable" class="table text-nowrap w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Registration Date</th>
                            <th>Package($)</th>
                            <th>Team Business($)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach($direct as $member)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $member->userid }}</td>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->doj }}</td>
                            <td>{{ round($member->shares, 2) }}</td>
                            <td>{{ round($member->teamtotal, 2) }}</td>
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
                            <th>Package($)</th>
                            <th>Team Business($)</th>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#directTeamTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'asc']]
    });
});
</script>
@endsection
