@extends('user.layouts.app')

@section('title', 'Stake Package History')

@section('content')
<div class="container-fluid content-inner mt-4 py-4">
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="/User/Dashboard">DASHBOARD</a></li>
        <li class="breadcrumb-item active">Locking Package History</li>
    </ul>
    
    <h1 class="page-header">Locking Package History</h1>
    <hr class="mb-4">

    <!-- Stake Package History Table -->
    <div class="card mb-5">
        <div class="card-body">
            <p>Here is your complete stake package transaction history.</p>
            <div class="table-responsive">
                <table id="stakeHistoryTable" class="table text-nowrap w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Amount ($)</th>
                            <th>Amount (MBZ)</th>
                            <th>Maturity Date</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach($basic as $record)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $record->userid }}</td>
                            <td>{{ $record->usersname }}</td>
                            <td>{{ round($record->usdt, 2) }}</td>
                            <td>{{ round($record->amount, 2) }}</td>
                             <td>{{ \Carbon\Carbon::parse($record->maturity_date)->format('d-m-Y') }}</td>
                            <td>{{ $record->created_at }}</td>
                            <td>{{ $record->status }}</td>
                        </tr>
                        @php $i++; @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Amount ($)</th>
                            <th>Amount (MBZ)</th>
                            <th>Return</th>
                            <th>Date</th>
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

<!-- jQuery & DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#stakeHistoryTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'asc']],
        language: {
            emptyTable: "No stake package history available."
        }
    });
});
</script>
@endsection
