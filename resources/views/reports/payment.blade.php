@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Provider Payment & Earnings Report</h3>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" class="row mb-4 p-3 bg-light rounded">
                        <div class="col-md-2">
                            <label class="form-label">Period</label>
                            <select name="period" class="form-control">
                                <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Provider</label>
                            <select name="provider_id" class="form-control">
                                <option value="">All Providers</option>
                                @foreach(\App\Models\Provider::all() as $provider)
                                    <option value="{{ $provider->id }}" {{ request('provider_id') == $provider->id ? 'selected' : '' }}>
                                        {{ $provider->name_of_manager }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-2 align-self-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                        {{-- <div class="col-md-2 align-self-end">
                            <a href="{{ route('payment.report.export', request()->all()) }}" class="btn btn-success">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </div> --}}
                    </form>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5>Total Providers</h5>
                                    <h3>{{ $summary['total_providers'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5>Total Appointments</h5>
                                    <h3>{{ number_format($summary['total_appointments']) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5>Total Revenue</h5>
                                    <h3>${{ number_format($summary['total_amount'], 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5>Total Commission</h5>
                                    <h3>${{ number_format($summary['total_commission'], 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Provider Reports -->
                    @foreach($providers as $providerData)
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-tie"></i> {{ $providerData['provider']->name_of_manager }}
                                        <small class="text-light">({{ $providerData['provider']->phone }})</small>
                                    </h5>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge bg-light text-dark">
                                        Wallet Balance: ${{ number_format($providerData['summary']['wallet_balance'], 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Provider Summary -->
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-light rounded">
                                        <small class="text-muted">Appointments</small>
                                        <h6 class="mb-0">{{ $providerData['summary']['total_appointments'] }}</h6>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-light rounded">
                                        <small class="text-muted">Total Revenue</small>
                                        <h6 class="mb-0">${{ number_format($providerData['summary']['total_amount'], 2) }}</h6>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-light rounded">
                                        <small class="text-muted">Commission</small>
                                        <h6 class="mb-0">${{ number_format($providerData['summary']['total_commission'], 2) }}</h6>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-light rounded">
                                        <small class="text-muted">Provider Earnings</small>
                                        <h6 class="mb-0">${{ number_format($providerData['summary']['total_provider_earnings'], 2) }}</h6>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-light rounded">
                                        <small class="text-muted">Wallet In</small>
                                        <h6 class="mb-0 text-success">${{ number_format($providerData['summary']['wallet_transactions_in'], 2) }}</h6>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-2 bg-light rounded">
                                        <small class="text-muted">Wallet Out</small>
                                        <h6 class="mb-0 text-danger">${{ number_format($providerData['summary']['wallet_transactions_out'], 2) }}</h6>
                                    </div>
                                </div>
                            </div>

                            <!-- Period Breakdown -->
                            @if($providerData['period_data']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>{{ ucfirst($period) }}</th>
                                            <th>Appointments</th>
                                            <th>Total Amount</th>
                                            <th>Commission</th>
                                            <th>Provider Earnings</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($providerData['period_data'] as $periodKey => $data)
                                        <tr>
                                            <td><strong>{{ $periodKey }}</strong></td>
                                            <td>{{ $data['appointments_count'] }}</td>
                                            <td>${{ number_format($data['total_amount'], 2) }}</td>
                                            <td>${{ number_format($data['commission'], 2) }}</td>
                                            <td>${{ number_format($data['provider_earnings'], 2) }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="toggleDetails('{{ $providerData['provider']->id }}-{{ $periodKey }}')">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Detailed appointments for this period (hidden by default) -->
                                        <tr id="details-{{ $providerData['provider']->id }}-{{ $periodKey }}" style="display: none;">
                                            <td colspan="6">
                                                <div class="p-3 bg-light">
                                                    <h6>Appointments for {{ $periodKey }}</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-xs">
                                                            <thead>
                                                                <tr>
                                                                    <th>ID</th>
                                                                    <th>Date</th>
                                                                    <th>Payment Type</th>
                                                                    <th>Total</th>
                                                                    <th>Commission</th>
                                                                    <th>Provider Earnings</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($data['appointments'] as $appointment)
                                                                <tr>
                                                                    <td>{{ $appointment['id'] }}</td>
                                                                    <td>{{ \Carbon\Carbon::parse($appointment['date'])->format('Y-m-d H:i') }}</td>
                                                                    <td>
                                                                        <span class="badge bg-secondary">{{ ucfirst($appointment['payment_type']) }}</span>
                                                                    </td>
                                                                    <td>${{ number_format($appointment['total'], 2) }}</td>
                                                                    <td>${{ number_format($appointment['commission'], 2) }}</td>
                                                                    <td>${{ number_format($appointment['provider_earnings'], 2) }}</td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No appointments found for this provider in the selected period.
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    @if($providers->count() == 0)
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle"></i> No data found for the selected criteria.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDetails(id) {
    const detailsRow = document.getElementById('details-' + id);
    if (detailsRow.style.display === 'none') {
        detailsRow.style.display = 'table-row';
    } else {
        detailsRow.style.display = 'none';
    }
}

// Auto-submit form when period changes
document.querySelector('select[name="period"]').addEventListener('change', function() {
    this.form.submit();
});
</script>
@endsection