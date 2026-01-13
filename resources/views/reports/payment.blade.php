@extends('layouts.admin')

@section('content')
<div class="container-fluid" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> {{ __('payment_report.page_title') }}
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Current Cycle Alert -->
                    @if($currentCycle)
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <strong>{{ __('payment_report.current_cycle') }}:</strong> 
                        {{ $currentCycle['period'] }}
                        <span class="badge bg-warning text-dark ms-2">
                            {{ __('payment_report.days_remaining') }}: {{ $currentCycle['days_remaining'] }}
                        </span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <!-- Filter Form -->
                    <form method="GET" class="row g-3 mb-4 p-3 bg-light rounded border">
                        <div class="col-12">
                            <h5 class="mb-3">
                                <i class="fas fa-filter"></i> {{ __('payment_report.filters') }}
                            </h5>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('payment_report.period') }}</label>
                            <select name="period" class="form-select" onchange="this.form.submit()">
                                <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>
                                    {{ __('payment_report.daily') }}
                                </option>
                                <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>
                                    {{ __('payment_report.monthly') }}
                                </option>
                                <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>
                                    {{ __('payment_report.yearly') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('payment_report.provider') }}</label>
                            <select name="provider_id" class="form-select">
                                <option value="">{{ __('payment_report.all_providers') }}</option>
                                @foreach($allProviders as $provider)
                                    <option value="{{ $provider->id }}" {{ request('provider_id') == $provider->id ? 'selected' : '' }}>
                                        {{ $provider->name_of_manager }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('payment_report.date_from') }}</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('payment_report.date_to') }}</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('payment_report.settlement_cycle') }}</label>
                            <select name="settlement_cycle_id" class="form-select">
                                <option value="">{{ __('payment_report.all_cycles') }}</option>
                                @foreach($settlementCycles as $cycle)
                                    <option value="{{ $cycle['id'] }}" {{ request('settlement_cycle_id') == $cycle['id'] ? 'selected' : '' }}>
                                        {{ $cycle['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('payment_report.settlement_status') }}</label>
                            <select name="settlement_status" class="form-select">
                                <option value="">{{ __('payment_report.all_status') }}</option>
                                <option value="1" {{ request('settlement_status') == '1' ? 'selected' : '' }}>
                                    {{ __('payment_report.pending_settlement') }}
                                </option>
                                <option value="2" {{ request('settlement_status') == '2' ? 'selected' : '' }}>
                                    {{ __('payment_report.settled') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> {{ __('payment_report.filter_btn') }}
                            </button>
                            <a href="{{ route('payment.report') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> {{ __('payment_report.reset_btn') }}
                            </a>
                            {{-- <a href="{{ route('payment.report.export', request()->all()) }}" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> {{ __('payment_report.export_btn') }}
                            </a> --}}
                        </div>
                    </form>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ __('payment_report.total_providers') }}</h6>
                                            <h3 class="mb-0 mt-2">{{ number_format($summary['total_providers']) }}</h3>
                                        </div>
                                        <div>
                                            <i class="fas fa-users fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ __('payment_report.total_appointments') }}</h6>
                                            <h3 class="mb-0 mt-2">{{ number_format($summary['total_appointments']) }}</h3>
                                        </div>
                                        <div>
                                            <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ __('payment_report.total_revenue') }}</h6>
                                            <h3 class="mb-0 mt-2">{{ number_format($summary['total_amount'], 2) }} JD</h3>
                                        </div>
                                        <div>
                                            <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ __('payment_report.total_commission') }}</h6>
                                            <h3 class="mb-0 mt-2">{{ number_format($summary['total_commission'], 2) }} JD</h3>
                                        </div>
                                        <div>
                                            <i class="fas fa-percentage fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card bg-secondary text-white h-100">
                                <div class="card-body">
                                    <h6 class="mb-2">{{ __('payment_report.total_provider_earnings') }}</h6>
                                    <h3 class="mb-0">{{ number_format($summary['total_provider_earnings'], 2) }} JD</h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card bg-dark text-white h-100">
                                <div class="card-body">
                                    <h6 class="mb-2">{{ __('payment_report.pending_settlements') }}</h6>
                                    <h3 class="mb-0">{{ number_format($summary['pending_settlements']) }}</h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card bg-teal text-white h-100">
                                <div class="card-body">
                                    <h6 class="mb-2">{{ __('payment_report.completed_settlements') }}</h6>
                                    <h3 class="mb-0">{{ number_format($summary['completed_settlements']) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Provider Reports -->
                    @foreach($providers as $providerData)
                    <div class="card mb-4 border-start border-primary border-4">
                        <div class="card-header bg-gradient-primary text-white">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-tie"></i> 
                                        {{ $providerData['provider']->name_of_manager }}
                                    </h5>
                                    <small>
                                        <i class="fas fa-phone"></i> {{ $providerData['provider']->phone }}
                                    </small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <span class="badge bg-light text-dark fs-6">
                                        <i class="fas fa-wallet"></i> 
                                        {{ __('payment_report.wallet_balance') }}: 
                                        {{ number_format($providerData['summary']['wallet_balance'], 2) }} JD
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Provider Summary -->
                            <div class="row mb-3">
                                <div class="col-md-2 mb-2">
                                    <div class="text-center p-3 bg-light rounded border">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                        <div class="small text-muted mt-1">{{ __('payment_report.appointments') }}</div>
                                        <h6 class="mb-0 fw-bold">{{ number_format($providerData['summary']['total_appointments']) }}</h6>
                                    </div>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <div class="text-center p-3 bg-light rounded border">
                                        <i class="fas fa-money-bill text-success"></i>
                                        <div class="small text-muted mt-1">{{ __('payment_report.total_amount') }}</div>
                                        <h6 class="mb-0 fw-bold">{{ number_format($providerData['summary']['total_amount'], 2) }}</h6>
                                    </div>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <div class="text-center p-3 bg-light rounded border">
                                        <i class="fas fa-percent text-warning"></i>
                                        <div class="small text-muted mt-1">{{ __('payment_report.commission') }}</div>
                                        <h6 class="mb-0 fw-bold">{{ number_format($providerData['summary']['total_commission'], 2) }}</h6>
                                    </div>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <div class="text-center p-3 bg-light rounded border">
                                        <i class="fas fa-hand-holding-usd text-info"></i>
                                        <div class="small text-muted mt-1">{{ __('payment_report.net_earnings') }}</div>
                                        <h6 class="mb-0 fw-bold">{{ number_format($providerData['summary']['total_provider_earnings'], 2) }}</h6>
                                    </div>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <div class="text-center p-3 bg-light rounded border">
                                        <i class="fas fa-arrow-down text-success"></i>
                                        <div class="small text-muted mt-1">{{ __('payment_report.wallet_in') }}</div>
                                        <h6 class="mb-0 fw-bold text-success">{{ number_format($providerData['summary']['wallet_transactions_in'], 2) }}</h6>
                                    </div>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <div class="text-center p-3 bg-light rounded border">
                                        <i class="fas fa-arrow-up text-danger"></i>
                                        <div class="small text-muted mt-1">{{ __('payment_report.wallet_out') }}</div>
                                        <h6 class="mb-0 fw-bold text-danger">{{ number_format($providerData['summary']['wallet_transactions_out'], 2) }}</h6>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Type Breakdown -->
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-chart-pie"></i> {{ __('payment_report.payment_breakdown') }}
                                    </h6>
                                    <div class="row">
                                        @foreach($providerData['payment_breakdown'] as $type => $data)
                                        <div class="col-md-4 mb-2">
                                            <div class="card border-{{ $type == 'cash' ? 'success' : ($type == 'visa' ? 'primary' : 'warning') }}">
                                                <div class="card-body p-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong class="text-{{ $type == 'cash' ? 'success' : ($type == 'visa' ? 'primary' : 'warning') }}">
                                                                {{ __('payment_report.'.$type) }}
                                                            </strong>
                                                            <div class="small">
                                                                {{ __('payment_report.count') }}: {{ $data['count'] }}
                                                            </div>
                                                        </div>
                                                        <div class="text-end">
                                                            <div class="fw-bold">{{ number_format($data['amount'], 2) }} JD</div>
                                                            <div class="small text-muted">
                                                                {{ __('payment_report.commission') }}: {{ number_format($data['commission'], 2) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Settlement Cycles for this Provider -->
                            @if($providerData['provider_settlements']->count() > 0)
                            <div class="card bg-info bg-opacity-10 mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-sync-alt"></i> {{ __('payment_report.settlement_cycles') }}
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-secondary">
                                                <tr>
                                                    <th>{{ __('payment_report.cycle_period') }}</th>
                                                    <th>{{ __('payment_report.appointments') }}</th>
                                                    <th>{{ __('payment_report.total_amount') }}</th>
                                                    <th>{{ __('payment_report.commission') }}</th>
                                                    <th>{{ __('payment_report.net_amount') }}</th>
                                                    <th>{{ __('payment_report.status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($providerData['provider_settlements'] as $settlement)
                                                <tr>
                                                    <td><strong>{{ $settlement['period'] }}</strong></td>
                                                    <td>{{ $settlement['total_appointments'] }}</td>
                                                    <td>{{ number_format($settlement['total_amount'], 2) }} JD</td>
                                                    <td>{{ number_format($settlement['commission'], 2) }} JD</td>
                                                    <td>{{ number_format($settlement['net_amount'], 2) }} JD</td>
                                                    <td>
                                                        @if($settlement['payment_status'] == 1)
                                                            <span class="badge bg-warning">{{ __('payment_report.unpaid') }}</span>
                                                        @else
                                                            <span class="badge bg-success">{{ __('payment_report.paid') }}</span>
                                                        @endif
                                                        
                                                        @if($settlement['cycle_status'] == 1)
                                                            <span class="badge bg-info">Active</span>
                                                        @else
                                                            <span class="badge bg-secondary">Completed</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Period Breakdown -->
                            @if($providerData['period_data']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>{{ ucfirst($period) }}</th>
                                            <th>{{ __('payment_report.appointments') }}</th>
                                            <th>{{ __('payment_report.total_amount') }}</th>
                                            <th>{{ __('payment_report.commission') }}</th>
                                            <th>{{ __('payment_report.provider_earnings') }}</th>
                                            <th>{{ __('payment_report.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($providerData['period_data'] as $periodKey => $data)
                                        <tr>
                                            <td><strong>{{ $periodKey }}</strong></td>
                                            <td>
                                                <span class="badge bg-primary">{{ $data['appointments_count'] }}</span>
                                            </td>
                                            <td>{{ number_format($data['total_amount'], 2) }} JD</td>
                                            <td>{{ number_format($data['commission'], 2) }} JD</td>
                                            <td>{{ number_format($data['provider_earnings'], 2) }} JD</td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="toggleDetails('{{ $providerData['provider']->id }}-{{ str_replace(' ', '-', $periodKey) }}')">
                                                    <i class="fas fa-eye"></i> {{ __('payment_report.details') }}
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Detailed appointments -->
                                        <tr id="details-{{ $providerData['provider']->id }}-{{ str_replace(' ', '-', $periodKey) }}" style="display: none;">
                                            <td colspan="6">
                                                <div class="p-3 bg-light">
                                                    <h6>
                                                        <i class="fas fa-list"></i> 
                                                        {{ __('payment_report.appointments_for') }} {{ $periodKey }}
                                                    </h6>
                                                    
                                                    <!-- Payment breakdown for this period -->
                                                    <div class="row mb-3">
                                                        @foreach($data['payment_breakdown'] as $type => $typeData)
                                                        @if($typeData['count'] > 0)
                                                        <div class="col-md-4">
                                                            <div class="alert alert-{{ $type == 'cash' ? 'success' : ($type == 'visa' ? 'primary' : 'warning') }} mb-0 p-2">
                                                                <strong>{{ __('payment_report.'.$type) }}:</strong>
                                                                {{ $typeData['count'] }} {{ __('payment_report.appointments') }} - 
                                                                {{ number_format($typeData['amount'], 2) }} JD
                                                            </div>
                                                        </div>
                                                        @endif
                                                        @endforeach
                                                    </div>
                                                    
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-striped">
                                                            <thead class="table-secondary">
                                                                <tr>
                                                                    <th>{{ __('payment_report.appointment_number') }}</th>
                                                                    <th>{{ __('payment_report.date') }}</th>
                                                                    <th>{{ __('payment_report.user') }}</th>
                                                                    <th>{{ __('payment_report.payment_type') }}</th>
                                                                    <th>{{ __('payment_report.total') }}</th>
                                                                    <th>{{ __('payment_report.commission') }}</th>
                                                                    <th>{{ __('payment_report.net_earnings') }}</th>
                                                                    <th>{{ __('payment_report.settlement_status') }}</th>
                                                                    <th>{{ __('payment_report.settlement_cycle') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($data['appointments'] as $appointment)
                                                                <tr>
                                                                    <td>
                                                                        <a href="{{ route('appointments.show', $appointment['id']) }}" target="_blank">
                                                                            #{{ $appointment['number'] }}
                                                                        </a>
                                                                    </td>
                                                                    <td>{{ \Carbon\Carbon::parse($appointment['date'])->format('Y-m-d H:i') }}</td>
                                                                    <td>{{ $appointment['user_name'] }}</td>
                                                                    <td>
                                                                        <span class="badge bg-{{ $appointment['payment_type'] == 'cash' ? 'success' : ($appointment['payment_type'] == 'visa' ? 'primary' : 'warning') }}">
                                                                            {{ __('payment_report.'.$appointment['payment_type']) }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ number_format($appointment['total'], 2) }} JD</td>
                                                                    <td class="text-danger">{{ number_format($appointment['commission'], 2) }} JD</td>
                                                                    <td class="text-success fw-bold">{{ number_format($appointment['provider_earnings'], 2) }} JD</td>
                                                                    <td>
                                                                        @if($appointment['settlement_status'] == 1)
                                                                            <span class="badge bg-warning">{{ __('payment_report.pending_settlement') }}</span>
                                                                        @else
                                                                            <span class="badge bg-success">{{ __('payment_report.settled') }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if($appointment['settlement_cycle'])
                                                                            <small class="text-muted">
                                                                                {{ $appointment['settlement_cycle']['period'] }}
                                                                                <span class="badge bg-{{ $appointment['settlement_cycle']['status'] == 'active' ? 'info' : 'secondary' }} ms-1">
                                                                                    {{ ucfirst($appointment['settlement_cycle']['status']) }}
                                                                                </span>
                                                                            </small>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
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
                                <i class="fas fa-info-circle"></i> {{ __('payment_report.no_appointments') }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    @if($providers->count() == 0)
                    <div class="alert alert-warning text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <h5>{{ __('payment_report.no_data') }}</h5>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-teal {
    background-color: #20c997;
}

.opacity-50 {
    opacity: 0.5;
}

/* RTL Support */
[dir="rtl"] .text-end {
    text-align: left !important;
}

[dir="rtl"] .text-start {
    text-align: right !important;
}

[dir="rtl"] .ms-2 {
    margin-left: 0 !important;
    margin-right: 0.5rem !important;
}

[dir="rtl"] .ms-1 {
    margin-left: 0 !important;
    margin-right: 0.25rem !important;
}
</style>

<script>
function toggleDetails(id) {
    const detailsRow = document.getElementById('details-' + id);
    const button = event.target.closest('button');
    const icon = button.querySelector('i');
    
    if (detailsRow.style.display === 'none') {
        detailsRow.style.display = 'table-row';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        button.querySelector('.btn-text')?.textContent = '{{ __("payment_report.hide_details") }}';
    } else {
        detailsRow.style.display = 'none';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        button.querySelector('.btn-text')?.textContent = '{{ __("payment_report.details") }}';
    }
}

// Print functionality
function printReport() {
    window.print();
}

@media print {
    .no-print {
        display: none !important;
    }
}
</script>
@endsection