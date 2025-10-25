@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> {{ __('messages.Orders_Report') }}
                    </h3>
                </div>

                <!-- Filter Form -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('reports.orders.generate') }}" class="row g-3">
                        <!-- Date Range -->
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.Start_Date') }} <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="start_date" 
                                   class="form-control" 
                                   value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}"
                                   required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.End_Date') }} <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="end_date" 
                                   class="form-control" 
                                   value="{{ request('end_date', now()->format('Y-m-d')) }}"
                                   required>
                        </div>

                        <!-- Order Status Filter -->
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.Order_Status') }}</label>
                            <select name="order_status[]" class="form-control" multiple>
                                <option value="1" {{ in_array('1', request('order_status', [])) ? 'selected' : '' }}>
                                    {{ __('messages.Pending') }}
                                </option>
                                <option value="2" {{ in_array('2', request('order_status', [])) ? 'selected' : '' }}>
                                    {{ __('messages.Accepted') }}
                                </option>
                                <option value="3" {{ in_array('3', request('order_status', [])) ? 'selected' : '' }}>
                                    {{ __('messages.On_The_Way') }}
                                </option>
                                <option value="4" {{ in_array('4', request('order_status', [])) ? 'selected' : '' }}>
                                    {{ __('messages.Delivered') }}
                                </option>
                                <option value="5" {{ in_array('5', request('order_status', [])) ? 'selected' : '' }}>
                                    {{ __('messages.Canceled') }}
                                </option>
                                <option value="6" {{ in_array('6', request('order_status', [])) ? 'selected' : '' }}>
                                    {{ __('messages.Refund') }}
                                </option>
                            </select>
                        </div>

                        <!-- Payment Status -->
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.Payment_Status') }}</label>
                            <select name="payment_status" class="form-control">
                                <option value="">{{ __('messages.All') }}</option>
                                <option value="1" {{ request('payment_status') == '1' ? 'selected' : '' }}>
                                    {{ __('messages.Paid') }}
                                </option>
                                <option value="2" {{ request('payment_status') == '2' ? 'selected' : '' }}>
                                    {{ __('messages.Unpaid') }}
                                </option>
                            </select>
                        </div>

                        <!-- Payment Type -->
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.Payment_Type') }}</label>
                            <select name="payment_type" class="form-control">
                                <option value="">{{ __('messages.All') }}</option>
                                @if(isset($paymentTypes))
                                    @foreach($paymentTypes as $type)
                                        <option value="{{ $type }}" {{ request('payment_type') == $type ? 'selected' : '' }}>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Customer Filter -->
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.Customer') }}</label>
                            <select name="user_id" class="form-control">
                                <option value="">{{ __('messages.All_Customers') }}</option>
                                @if(isset($users))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Delivery Filter -->
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.Delivery') }}</label>
                            <select name="delivery_id" class="form-control">
                                <option value="">{{ __('messages.All_Deliveries') }}</option>
                                @if(isset($deliveries))
                                    @foreach($deliveries as $delivery)
                                        <option value="{{ $delivery->id }}" {{ request('delivery_id') == $delivery->id ? 'selected' : '' }}>
                                            {{ $delivery->place }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Report Type -->
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.Report_Type') }}</label>
                            <select name="report_type" class="form-control" required>
                                <option value="summary" {{ request('report_type', 'summary') == 'summary' ? 'selected' : '' }}>
                                    {{ __('messages.Summary_Report') }}
                                </option>
                                <option value="detailed" {{ request('report_type') == 'detailed' ? 'selected' : '' }}>
                                    {{ __('messages.Detailed_Report') }}
                                </option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> {{ __('messages.Generate_Report') }}
                            </button>
                            <a href="{{ route('reports.orders.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> {{ __('messages.Clear') }}
                            </a>
                        </div>
                    </form>
                </div>

                @if(isset($summary))
                    <!-- Summary Statistics -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3">
                                    <i class="fas fa-chart-bar"></i> {{ __('messages.Summary_Statistics') }}
                                    <small class="text-muted">({{ $summary['date_range'] }})</small>
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Revenue Cards -->
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3>{{ number_format($summary['total_revenue'], 2) }} {{ __('messages.Currency') }}</h3>
                                        <p>{{ __('messages.Total_Revenue') }}</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3>{{ $summary['total_orders'] }}</h3>
                                        <p>{{ __('messages.Total_Orders') }}</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3>{{ number_format($summary['average_order_value'], 2) }} {{ __('messages.Currency') }}</h3>
                                        <p>{{ __('messages.Average_Order_Value') }}</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3>{{ number_format($summary['total_discounts'] + $summary['total_coupon_discounts'], 2) }} {{ __('messages.Currency') }}</h3>
                                        <p>{{ __('messages.Total_Discounts') }}</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-percent"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Status Breakdown -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">{{ __('messages.Order_Status_Breakdown') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <span class="badge bg-secondary p-2">{{ $summary['pending_orders'] }}</span>
                                                    <br><small>{{ __('messages.Pending') }}</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <span class="badge bg-primary p-2">{{ $summary['accepted_orders'] }}</span>
                                                    <br><small>{{ __('messages.Accepted') }}</small>
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <div class="text-center">
                                                    <span class="badge bg-info p-2">{{ $summary['on_the_way_orders'] }}</span>
                                                    <br><small>{{ __('messages.On_The_Way') }}</small>
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <div class="text-center">
                                                    <span class="badge bg-success p-2">{{ $summary['delivered_orders'] }}</span>
                                                    <br><small>{{ __('messages.Delivered') }}</small>
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <div class="text-center">
                                                    <span class="badge bg-warning p-2">{{ $summary['canceled_orders'] }}</span>
                                                    <br><small>{{ __('messages.Canceled') }}</small>
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <div class="text-center">
                                                    <span class="badge bg-danger p-2">{{ $summary['refund_orders'] }}</span>
                                                    <br><small>{{ __('messages.Refund') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">{{ __('messages.Payment_Status') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <span class="badge bg-success p-3">{{ $summary['paid_orders'] }}</span>
                                                    <br><small>{{ __('messages.Paid') }}</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <span class="badge bg-danger p-3">{{ $summary['unpaid_orders'] }}</span>
                                                    <br><small>{{ __('messages.Unpaid') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daily Stats Chart (for summary reports) -->
                        @if(request('report_type', 'summary') == 'summary' && isset($dailyStats) && $dailyStats->isNotEmpty())
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">{{ __('messages.Daily_Statistics') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('messages.Date') }}</th>
                                                            <th>{{ __('messages.Orders') }}</th>
                                                            <th>{{ __('messages.Revenue') }}</th>
                                                            <th>{{ __('messages.Avg_Order_Value') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($dailyStats as $stat)
                                                            <tr>
                                                                <td>{{ $stat->order_date }}</td>
                                                                <td>{{ $stat->orders_count }}</td>
                                                                <td>{{ number_format($stat->daily_revenue, 2) }} {{ __('messages.Currency') }}</td>
                                                                <td>{{ number_format($stat->avg_order_value, 2) }} {{ __('messages.Currency') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Top Products (for summary reports) -->
                        @if(request('report_type', 'summary') == 'summary' && isset($topProducts) && $topProducts->isNotEmpty())
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">{{ __('messages.Top_Products') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('messages.Product') }}</th>
                                                            <th>{{ __('messages.Quantity_Sold') }}</th>
                                                            <th>{{ __('messages.Revenue') }}</th>
                                                            <th>{{ __('messages.Orders') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($topProducts as $product)
                                                            <tr>
                                                                <td>{{ app()->getLocale() == 'ar' ? $product->name_ar : $product->name_en }}</td>
                                                                <td>{{ $product->total_quantity }}</td>
                                                                <td>{{ number_format($product->total_revenue, 2) }} {{ __('messages.Currency') }}</td>
                                                                <td>{{ $product->orders_count }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Export Button -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <form method="GET" action="{{ route('reports.orders.export') }}" class="d-inline">
                                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                    @foreach(request('order_status', []) as $status)
                                        <input type="hidden" name="order_status[]" value="{{ $status }}">
                                    @endforeach
                                    @if(request('payment_status'))
                                        <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
                                    @endif
                                    @if(request('payment_type'))
                                        <input type="hidden" name="payment_type" value="{{ request('payment_type') }}">
                                    @endif
                                    @if(request('user_id'))
                                        <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                                    @endif
                                    @if(request('delivery_id'))
                                        <input type="hidden" name="delivery_id" value="{{ request('delivery_id') }}">
                                    @endif
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-download"></i> {{ __('messages.Export_CSV') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Detailed Orders Table -->
                @if(isset($orders) && $orders->isNotEmpty())
                    <div class="card-body">
                        <h5 class="mb-3">{{ __('messages.Detailed_Orders') }}</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.Order_Number') }}</th>
                                        <th>{{ __('messages.Date') }}</th>
                                        <th>{{ __('messages.Customer') }}</th>
                                        <th>{{ __('messages.Status') }}</th>
                                        <th>{{ __('messages.Payment') }}</th>
                                        <th>{{ __('messages.Delivery') }}</th>
                                        <th>{{ __('messages.Total') }}</th>
                                        <th>{{ __('messages.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <strong>#{{ $order->number }}</strong>
                                            </td>
                                            <td>{{ Carbon\Carbon::parse($order->date)->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div>
                                                    <strong>{{ $order->customer_name }}</strong>
                                                    <br><small class="text-muted">{{ $order->customer_email }}</small>
                                                    @if($order->customer_phone)
                                                        <br><small class="text-muted">{{ $order->customer_phone }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @switch($order->order_status)
                                                    @case(1)
                                                        <span class="badge bg-secondary">{{ __('messages.Pending') }}</span>
                                                        @break
                                                    @case(2)
                                                        <span class="badge bg-primary">{{ __('messages.Accepted') }}</span>
                                                        @break
                                                    @case(3)
                                                        <span class="badge bg-info">{{ __('messages.On_The_Way') }}</span>
                                                        @break
                                                    @case(4)
                                                        <span class="badge bg-success">{{ __('messages.Delivered') }}</span>
                                                        @break
                                                    @case(5)
                                                        <span class="badge bg-warning">{{ __('messages.Canceled') }}</span>
                                                        @break
                                                    @case(6)
                                                        <span class="badge bg-danger">{{ __('messages.Refund') }}</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <div>
                                                    @if($order->payment_status == 1)
                                                        <span class="badge bg-success">{{ __('messages.Paid') }}</span>
                                                    @else
                                                        <span class="badge bg-danger">{{ __('messages.Unpaid') }}</span>
                                                    @endif
                                                    <br><small class="text-muted">{{ ucfirst($order->payment_type) }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($order->delivery_place)
                                                    <span class="badge bg-info">{{ $order->delivery_place }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ number_format($order->total_prices, 2) }} {{ __('messages.Currency') }}</strong>
                                                    @if($order->total_discounts > 0)
                                                        <br><small class="text-success">-{{ number_format($order->total_discounts, 2) }} {{ __('messages.Discount') }}</small>
                                                    @endif
                                                    @if($order->delivery_fee > 0)
                                                        <br><small class="text-info">+{{ number_format($order->delivery_fee, 2) }} {{ __('messages.Delivery') }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('orders.show', $order->id) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> {{ __('messages.View') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $orders->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when dates change for quick filtering
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    
    if (startDate && endDate) {
        startDate.addEventListener('change', function() {
            if (endDate.value && this.value) {
                // Auto-submit could be enabled here if desired
                // document.querySelector('form').submit();
            }
        });
    }
    
    // Set max date for start_date to end_date value
    if (startDate && endDate) {
        endDate.addEventListener('change', function() {
            startDate.max = this.value;
        });
        
        startDate.addEventListener('change', function() {
            endDate.min = this.value;
        });
    }
});
</script>
@endsection