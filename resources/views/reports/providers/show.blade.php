@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Provider Detailed Report</h1>
    
    <div class="row">
        <!-- Provider Info -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Provider Information
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $provider->name_of_manager }}
                            </div>
                            <div class="mt-2">
                                <p class="mb-1"><strong>Phone:</strong> {{ $provider->country_code }} {{ $provider->phone }}</p>
                                <p class="mb-1"><strong>Email:</strong> {{ $provider->email ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Balance:</strong> {{ number_format($provider->balance, 2) }} JOD</p>
                                <p class="mb-1"><strong>Status:</strong> 
                                    @if($provider->activate == 1)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-auto">
                            @if($provider->photo_of_manager)
                                <img src="{{ asset('storage/' . $provider->photo_of_manager) }}" 
                                     alt="Manager Photo" class="img-profile rounded-circle" width="80">
                            @else
                                <i class="fas fa-user-circle fa-3x text-gray-300"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Completed Orders -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Completed Orders (Last 30 Days)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $reportData['completed_orders_last_30_days'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Monthly Earnings -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Monthly Earnings
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                        {{ number_format($reportData['monthly_earnings']['total_after_commission'], 2) }} JOD
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="text-xs text-muted">
                                        (Total: {{ number_format($reportData['monthly_earnings']['total_earnings'], 2) }} JOD)
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Commission Section -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Glovana Commission</h6>
                </div>
                <div class="card-body">
                    <h4 class="small font-weight-bold">
                        Total Commission 
                        <span class="float-right">
                            {{ number_format($reportData['glovana_commission']['total_commission'], 2) }} JOD
                        </span>
                    </h4>
                    <div class="progress mb-4">
                        <div class="progress-bar bg-danger" role="progressbar" 
                             style="width: {{ $reportData['glovana_commission']['total_commission'] ? 100 : 0 }}%" 
                             aria-valuenow="{{ $reportData['glovana_commission']['total_commission'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="{{ $reportData['glovana_commission']['total_commission'] }}">
                        </div>
                    </div>
                    
                    <h4 class="small font-weight-bold">
                        Paid Commission 
                        <span class="float-right">
                            {{ number_format($reportData['glovana_commission']['paid_commission'], 2) }} JOD
                        </span>
                    </h4>
                    <div class="progress mb-4">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $reportData['glovana_commission']['total_commission'] ? ($reportData['glovana_commission']['paid_commission'] / $reportData['glovana_commission']['total_commission']) * 100 : 0 }}%" 
                             aria-valuenow="{{ $reportData['glovana_commission']['paid_commission'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="{{ $reportData['glovana_commission']['total_commission'] }}">
                        </div>
                    </div>
                    
                    <h4 class="small font-weight-bold">
                        Unpaid Commission 
                        <span class="float-right">
                            {{ number_format($reportData['glovana_commission']['unpaid_commission'], 2) }} JOD
                        </span>
                    </h4>
                    <div class="progress mb-4">
                        <div class="progress-bar bg-warning" role="progressbar" 
                             style="width: {{ $reportData['glovana_commission']['total_commission'] ? ($reportData['glovana_commission']['unpaid_commission'] / $reportData['glovana_commission']['total_commission']) * 100 : 0 }}%" 
                             aria-valuenow="{{ $reportData['glovana_commission']['unpaid_commission'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="{{ $reportData['glovana_commission']['total_commission'] }}">
                        </div>
                    </div>
                    
                    @if($reportData['payment_reminder'])
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> 
                            {{ $reportData['payment_reminder']['message'] }} ({{ number_format($reportData['payment_reminder']['amount_due'], 2) }} JOD)
                            <br>
                            <small>Oldest unpaid since: {{ $reportData['payment_reminder']['oldest_unpaid_date'] }}</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Financial Transactions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Financial Transactions</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['financial_transactions'] as $transaction)
                                <tr>
                                    <td>{{ $transaction['date'] }}</td>
                                    <td class="{{ $transaction['type'] == 'Deposit' ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($transaction['amount'], 2) }} JOD
                                    </td>
                                    <td>{{ $transaction['type'] }}</td>
                                    <td>{{ $transaction['note'] ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No transactions found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <a href="{{ route('admin.providers.report.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Providers List
    </a>
</div>
@endsection