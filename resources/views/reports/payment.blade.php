@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">Payment Report</h2>

    <form method="GET" class="row mb-4">
        <div class="col-md-3">
            <label>Date From</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-3">
            <label>Date To</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-3">
            <label>Payment Type</label>
            <select name="payment_type" class="form-control">
                <option value="">All</option>
                <option value="cash" {{ request('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="visa" {{ request('payment_type') == 'visa' ? 'selected' : '' }}>Visa</option>
                <option value="wallet" {{ request('payment_type') == 'wallet' ? 'selected' : '' }}>Wallet</option>
            </select>
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Provider</th>
                <th>Payment Type</th>
                <th>Status</th>
                <th>Total</th>
                <th>Commission</th>
                <th>Provider Earnings</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['appointments'] as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item['date'] }}</td>
                <td>{{ $item['provider'] }}</td>
                <td>{{ ucfirst($item['payment_type']) }}</td>
                <td>{{ $item['payment_status'] }}</td>
                <td>{{ number_format($item['total'], 2) }}</td>
                <td>{{ number_format($item['commission'], 2) }}</td>
                <td>{{ number_format($item['provider_earnings'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="table-light fw-bold">
            <tr>
                <td colspan="5">Total</td>
                <td>{{ number_format($report['total_amount'], 2) }}</td>
                <td>{{ number_format($report['total_commission'], 2) }}</td>
                <td>{{ number_format($report['total_provider_earnings'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
