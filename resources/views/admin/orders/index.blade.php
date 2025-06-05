@extends('layouts.admin')

@section('title', __('messages.Orders'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Orders') }}</h1>
        <a href="{{ route('orders.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.Add_New_Order') }}
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Filter_Orders') }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('orders.filter') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="user_id">{{ __('messages.User') }}</label>
                            <select class="form-control" id="user_id" name="user_id">
                                <option value="">{{ __('messages.All_Users') }}</option>
                                @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->phone }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="driver_id">{{ __('messages.Driver') }}</label>
                            <select class="form-control" id="driver_id" name="driver_id">
                                <option value="">{{ __('messages.All_Drivers') }}</option>
                                @foreach($drivers ?? [] as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }} ({{ $driver->phone }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="service_id">{{ __('messages.Service') }}</label>
                            <select class="form-control" id="service_id" name="service_id">
                                <option value="">{{ __('messages.All_Services') }}</option>
                                @foreach($services ?? [] as $service)
                                <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name_en }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">{{ __('messages.Status') }}</label>
                            <select class="form-control" id="status" name="status">
                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>{{ __('messages.All_Statuses') }}</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('messages.Pending') }}</option>
                                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>{{ __('messages.Driver_Accepted') }}</option>
                                <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>{{ __('messages.Driver_Going_To_User') }}</option>
                                <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>{{ __('messages.User_With_Driver') }}</option>
                                <option value="5" {{ request('status') == '5' ? 'selected' : '' }}>{{ __('messages.Delivered') }}</option>
                                <option value="6" {{ request('status') == '6' ? 'selected' : '' }}>{{ __('messages.User_Cancelled') }}</option>
                                <option value="7" {{ request('status') == '7' ? 'selected' : '' }}>{{ __('messages.Driver_Cancelled') }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="payment_method">{{ __('messages.Payment_Method') }}</label>
                            <select class="form-control" id="payment_method" name="payment_method">
                                <option value="all" {{ request('payment_method') == 'all' ? 'selected' : '' }}>{{ __('messages.All_Methods') }}</option>
                                <option value="1" {{ request('payment_method') == '1' ? 'selected' : '' }}>{{ __('messages.Cash') }}</option>
                                <option value="2" {{ request('payment_method') == '2' ? 'selected' : '' }}>{{ __('messages.Visa') }}</option>
                                <option value="3" {{ request('payment_method') == '3' ? 'selected' : '' }}>{{ __('messages.Wallet') }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status_payment">{{ __('messages.Payment_Status') }}</label>
                            <select class="form-control" id="status_payment" name="status_payment">
                                <option value="all" {{ request('status_payment') == 'all' ? 'selected' : '' }}>{{ __('messages.All') }}</option>
                                <option value="1" {{ request('status_payment') == '1' ? 'selected' : '' }}>{{ __('messages.Pending') }}</option>
                                <option value="2" {{ request('status_payment') == '2' ? 'selected' : '' }}>{{ __('messages.Paid') }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">{{ __('messages.Date_From') }}</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">{{ __('messages.Date_To') }}</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> {{ __('messages.Filter') }}
                        </button>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> {{ __('messages.Reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Summary Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.Total_Orders') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orders->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('messages.Completed_Orders') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orders->where('status', 5)->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                {{ __('messages.Cancelled_Orders') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orders->whereIn('status', [6, 7])->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
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
                                {{ __('messages.Total_Revenue') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $orders->where('status', 5)->sum('total_price_after_discount') }}
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

    <!-- Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Orders_List') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Date') }}</th>
                            <th>{{ __('messages.User') }}</th>
                            <th>{{ __('messages.Driver') }}</th>
                            <th>{{ __('messages.Service') }}</th>
                            <th>{{ __('messages.Price') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Payment') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($order->user)
                                <a href="{{ route('users.show', $order->user_id) }}">
                                    {{ $order->user->name }}
                                </a>
                                @else
                                {{ __('messages.Not_Available') }}
                                @endif
                            </td>
                            <td>
                                @if($order->driver)
                                <a href="{{ route('drivers.show', $order->driver_id) }}">
                                    {{ $order->driver->name }}
                                </a>
                                @else
                                {{ __('messages.Not_Assigned') }}
                                @endif
                            </td>
                            <td>
                                @if($order->service)
                                <a href="{{ route('services.show', $order->service_id) }}">
                                    {{ $order->service->name_en }}
                                </a>
                                @else
                                {{ __('messages.Not_Available') }}
                                @endif
                            </td>
                            <td>
                                {{ $order->total_price_after_discount }}
                                @if($order->discount_value > 0)
                                <span class="badge badge-info">
                                    -{{ $order->getFormattedDiscount() }} ({{ $order->getDiscountPercentage() }}%)
                                </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $order->getStatusClass() }}">
                                    {{ $order->getStatusText() }}
                                </span>
                            </td>
                            <td>
                                <div>
                                    <span class="badge badge-primary">{{ $order->getPaymentMethodText() }}</span>
                                </div>
                                <div class="mt-1">
                                    <span class="badge badge-{{ $order->getPaymentStatusClass() }}">
                                        {{ $order->getPaymentStatusText() }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-danger btn-sm" onclick="event.preventDefault(); if(confirm('{{ __('messages.Delete_Confirm') }}')) document.getElementById('delete-form-{{ $order->id }}').submit();">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <form id="delete-form-{{ $order->id }}" action="{{ route('orders.destroy', $order->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "order": [[0, "desc"]]
        });
        
        // Date validation
        $('#date_to').on('change', function() {
            var startDate = $('#date_from').val();
            var endDate = $(this).val();
            
            if (startDate && endDate && startDate > endDate) {
                alert("{{ __('messages.Date_Range_Error') }}");
                $(this).val('');
            }
        });
    });
</script>
@endsection