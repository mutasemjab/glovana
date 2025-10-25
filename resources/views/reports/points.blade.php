@extends('layouts.admin')

@section('title', __('messages.Points_Reports'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar text-primary"></i> {{ __('messages.Points_Reports') }}
        </h1>
       
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> {{ __('messages.Filters') }}
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.points') }}" class="row">
                <div class="col-md-3">
                    <label for="date_from">{{ __('messages.Date_From') }}</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to">{{ __('messages.Date_To') }}</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ $dateTo }}">
                </div>
                <div class="col-md-2">
                    <label for="filter_type">{{ __('messages.Transaction_Type') }}</label>
                    <select class="form-control" id="filter_type" name="filter_type">
                        <option value="all" {{ $filterType == 'all' ? 'selected' : '' }}>{{ __('messages.All_Types') }}</option>
                        <option value="earned" {{ $filterType == 'earned' ? 'selected' : '' }}>{{ __('messages.Earned_Only') }}</option>
                        <option value="deducted" {{ $filterType == 'deducted' ? 'selected' : '' }}>{{ __('messages.Deducted_Only') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="top_limit">{{ __('messages.Top_Users_Limit') }}</label>
                    <select class="form-control" id="top_limit" name="top_limit">
                        <option value="5" {{ $topLimit == 5 ? 'selected' : '' }}>{{ __('messages.Top_5') }}</option>
                        <option value="10" {{ $topLimit == 10 ? 'selected' : '' }}>{{ __('messages.Top_10') }}</option>
                        <option value="20" {{ $topLimit == 20 ? 'selected' : '' }}>{{ __('messages.Top_20') }}</option>
                        <option value="50" {{ $topLimit == 50 ? 'selected' : '' }}>{{ __('messages.Top_50') }}</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> {{ __('messages.Apply_Filters') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="row mb-4">
        <!-- Period Stats -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('messages.Period_Points_Earned') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                +{{ number_format($stats['period']->total_earned) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plus-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('messages.Period_Points_Deducted') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                -{{ number_format($stats['period']->total_deducted) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-minus-circle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('messages.Period_Transactions') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['period']->total_transactions) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.Active_Users_Period') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['period']->active_users) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Daily Activity Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Daily_Points_Activity') }}</h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyActivityChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Points Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Points_Distribution') }}</h6>
                </div>
                <div class="card-body">
                    <canvas id="pointsDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Users Tables -->
    <div class="row mb-4">
        <!-- Top Earners -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        {{ __('messages.Top_Points_Earners') }} ({{ __('messages.Period') }})
                    </h6>
                    <form method="GET" action="{{ route('reports.points') }}" class="form-inline">
                        @foreach(request()->except('sort_by') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <select name="sort_by" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="period_points" {{ $sortBy == 'period_points' ? 'selected' : '' }}>{{ __('messages.Period_Points') }}</option>
                            <option value="total_points" {{ $sortBy == 'total_points' ? 'selected' : '' }}>{{ __('messages.Total_Points') }}</option>
                            <option value="transactions_count" {{ $sortBy == 'transactions_count' ? 'selected' : '' }}>{{ __('messages.Transactions_Count') }}</option>
                            <option value="avg_transaction" {{ $sortBy == 'avg_transaction' ? 'selected' : '' }}>{{ __('messages.Avg_Transaction') }}</option>
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    @if($topEarners->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.Rank') }}</th>
                                        <th>{{ __('messages.User') }}</th>
                                        <th>{{ __('messages.Period_Points') }}</th>
                                        <th>{{ __('messages.Total_Points') }}</th>
                                        <th>{{ __('messages.Transactions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topEarners as $index => $user)
                                    <tr>
                                        <td>
                                            @if($index < 3)
                                                <span class="badge badge-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'dark') }}">
                                                    #{{ $index + 1 }}
                                                </span>
                                            @else
                                                <span class="text-muted">#{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($user->photo)
                                                    <img src="{{ asset('assets/admin/uploads/' . $user->photo) }}" 
                                                         class="rounded-circle mr-2" width="30" height="30">
                                                @else
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mr-2" 
                                                         style="width: 30px; height: 30px;">
                                                        <i class="fas fa-user text-white fa-xs"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-weight-bold">{{ $user->name }}</div>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-success font-weight-bold">
                                                +{{ number_format($user->period_points) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($user->total_points) }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $user->transactions_count }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-star fa-2x text-muted mb-2"></i>
                            <p class="text-muted">{{ __('messages.No_Earners_Found') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Spenders -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        {{ __('messages.Top_Points_Spenders') }} ({{ __('messages.Period') }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($topSpenders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.Rank') }}</th>
                                        <th>{{ __('messages.User') }}</th>
                                        <th>{{ __('messages.Period_Points') }}</th>
                                        <th>{{ __('messages.Total_Points') }}</th>
                                        <th>{{ __('messages.Transactions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topSpenders as $index => $user)
                                    <tr>
                                        <td>
                                            @if($index < 3)
                                                <span class="badge badge-{{ $index == 0 ? 'danger' : ($index == 1 ? 'warning' : 'secondary') }}">
                                                    #{{ $index + 1 }}
                                                </span>
                                            @else
                                                <span class="text-muted">#{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($user->photo)
                                                    <img src="{{ asset('assets/admin/uploads/' . $user->photo) }}" 
                                                         class="rounded-circle mr-2" width="30" height="30">
                                                @else
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mr-2" 
                                                         style="width: 30px; height: 30px;">
                                                        <i class="fas fa-user text-white fa-xs"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-weight-bold">{{ $user->name }}</div>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-warning font-weight-bold">
                                                -{{ number_format($user->period_points) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($user->total_points) }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $user->transactions_count }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-star fa-2x text-muted mb-2"></i>
                            <p class="text-muted">{{ __('messages.No_Spenders_Found') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Tables -->
    <div class="row mb-4">
       

        <!-- Top Providers -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        {{ __('messages.Top_Performing_Providers') }} ({{ __('messages.Period') }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($topProviders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.Provider') }}</th>
                                        <th>{{ __('messages.Transactions') }}</th>
                                        <th>{{ __('messages.Points_Added') }}</th>
                                        <th>{{ __('messages.Points_Deducted') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topProviders as $provider)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-info rounded-circle d-flex align-items-center justify-content-center mr-2" 
                                                     style="width: 30px; height: 30px;">
                                                    <i class="fas fa-store text-white fa-xs"></i>
                                                </div>
                                                <span class="font-weight-bold">{{ $provider->provider->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $provider->transactions_count }}</span>
                                        </td>
                                        <td>
                                            <span class="text-success">+{{ number_format($provider->points_added) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-warning">-{{ number_format($provider->points_deducted) }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-store fa-2x text-muted mb-2"></i>
                            <p class="text-muted">{{ __('messages.No_Provider_Activity') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Platform Statistics -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                {{ __('messages.Overall_Platform_Statistics') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="card bg-gradient-primary text-white">
                        <div class="card-body">
                            <h3 class="mb-0">{{ number_format($stats['overall']->total_users) }}</h3>
                            <small>{{ __('messages.Total_Platform_Users') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-gradient-success text-white">
                        <div class="card-body">
                            <h3 class="mb-0">{{ number_format($stats['overall']->platform_total_points) }}</h3>
                            <small>{{ __('messages.Total_Platform_Points') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-gradient-info text-white">
                        <div class="card-body">
                            <h3 class="mb-0">{{ number_format($stats['overall']->avg_user_points, 0) }}</h3>
                            <small>{{ __('messages.Average_Points_Per_User') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-gradient-warning text-white">
                        <div class="card-body">
                            <h3 class="mb-0">{{ number_format($stats['all_time']->all_time_transactions) }}</h3>
                            <small>{{ __('messages.All_Time_Transactions') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Daily Activity Chart
    const dailyActivityCtx = document.getElementById('dailyActivityChart').getContext('2d');
    const dailyActivityData = @json($dailyActivity);
    
    const dailyActivityChart = new Chart(dailyActivityCtx, {
        type: 'line',
        data: {
            labels: dailyActivityData.map(item => item.date),
            datasets: [
                {
                    label: "{{ __('messages.Points_Earned') }}",
                    data: dailyActivityData.map(item => item.earned),
                    borderColor: 'rgb(28, 200, 138)',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: "{{ __('messages.Points_Deducted') }}",
                    data: dailyActivityData.map(item => item.deducted),
                    borderColor: 'rgb(246, 194, 62)',
                    backgroundColor: 'rgba(246, 194, 62, 0.1)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + ' {{ __("messages.pts") }}';
                        }
                    }
                }
            }
        }
    });

    // Points Distribution Chart
    const distributionCtx = document.getElementById('pointsDistributionChart').getContext('2d');
    const distributionData = @json($pointsDistribution);
    
    const distributionChart = new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: distributionData.map(item => item.range_label),
            datasets: [{
                data: distributionData.map(item => item.user_count),
                backgroundColor: [
                    '#e74a3b',
                    '#f39c12',
                    '#f1c40f',
                    '#2ecc71',
                    '#3498db',
                    '#9b59b6'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' {{ __("messages.users") }} (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Auto-refresh every 5 minutes
    setInterval(function() {
        if (confirm("{{ __('messages.Auto_Refresh_Confirmation') }}")) {
            location.reload();
        }
    }, 300000); // 5 minutes
});
</script>
@endsection