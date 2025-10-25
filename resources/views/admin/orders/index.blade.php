@extends('layouts.admin')

@section('title', __('messages.orders'))

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('messages.orders') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('messages.orders') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('messages.total_orders') }}</span>
                            <h4 class="mb-3">{{ number_format($statistics['total_orders']) }}</h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title bg-primary rounded-circle">
                                    <i class="ri-shopping-cart-line font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('messages.pending_orders') }}</span>
                            <h4 class="mb-3">{{ number_format($statistics['pending_orders']) }}</h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-warning">
                                <span class="avatar-title bg-warning rounded-circle">
                                    <i class="ri-time-line font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('messages.delivered_orders') }}</span>
                            <h4 class="mb-3">{{ number_format($statistics['delivered_orders']) }}</h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-success">
                                <span class="avatar-title bg-success rounded-circle">
                                    <i class="ri-check-line font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('messages.total_revenue') }}</span>
                            <h4 class="mb-3">{{ number_format($statistics['total_revenue'], 2) }} {{ __('messages.jd') }}</h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-info">
                                <span class="avatar-title bg-info rounded-circle">
                                    <i class="ri-money-dollar-box-line font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('messages.filters') }}</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('orders.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.order_status') }}</label>
                                <select name="order_status" class="form-control">
                                    <option value="">{{ __('messages.all_statuses') }}</option>
                                    <option value="1" {{ request('order_status') == '1' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                                    <option value="2" {{ request('order_status') == '2' ? 'selected' : '' }}>{{ __('messages.accepted') }}</option>
                                    <option value="3" {{ request('order_status') == '3' ? 'selected' : '' }}>{{ __('messages.on_the_way') }}</option>
                                    <option value="4" {{ request('order_status') == '4' ? 'selected' : '' }}>{{ __('messages.delivered') }}</option>
                                    <option value="5" {{ request('order_status') == '5' ? 'selected' : '' }}>{{ __('messages.canceled') }}</option>
                                    <option value="6" {{ request('order_status') == '6' ? 'selected' : '' }}>{{ __('messages.refund') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.payment_status') }}</label>
                                <select name="payment_status" class="form-control">
                                    <option value="">{{ __('messages.all_statuses') }}</option>
                                    <option value="1" {{ request('payment_status') == '1' ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
                                    <option value="2" {{ request('payment_status') == '2' ? 'selected' : '' }}>{{ __('messages.unpaid') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.from_date') }}</label>
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.to_date') }}</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('messages.search') }}</label>
                                <input type="text" name="search" class="form-control" placeholder="{{ __('messages.search_orders') }}" value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-search-line"></i> {{ __('messages.filter') }}
                                </button>
                                <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                                    <i class="ri-refresh-line"></i> {{ __('messages.reset') }}
                                </a>
                              
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('messages.orders_list') }}</h4>
                </div>
                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('messages.order_number') }}</th>
                                        <th>{{ __('messages.customer') }}</th>
                                        <th>{{ __('messages.items_count') }}</th>
                                        <th>{{ __('messages.total_amount') }}</th>
                                        <th>{{ __('messages.order_status') }}</th>
                                        <th>{{ __('messages.payment_status') }}</th>
                                        <th>{{ __('messages.payment_type') }}</th>
                                        <th>{{ __('messages.date') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td><strong>#{{ $order->number }}</strong></td>
                                            <td>
                                                <div>{{ $order->user->name ?? __('messages.no_customer') }}</div>
                                                <small class="text-muted">{{ $order->user->phone ?? '' }}</small>
                                            </td>
                                            <td>{{ $order->items_count }}</td>
                                            <td>{{ number_format($order->total_prices, 2) }} {{ __('messages.jd') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $order->status_color }}">
                                                    {{ $order->order_status_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $order->payment_status == 1 ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $order->payment_status_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($order->payment_type) }}</span>
                                            </td>
                                            <td>{{ $order->date->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                   <a href="{{ route('orders.show', $order->id) }}" class="btn btn-info btn-sm" title="{{ __('messages.View') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-primary btn-sm" title="{{ __('messages.Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="row">
                            <div class="col-12">
                                {{ $orders->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ri-shopping-cart-line font-size-48 text-muted"></i>
                            <h5 class="mt-3">{{ __('messages.no_orders_found') }}</h5>
                            <p class="text-muted">{{ __('messages.no_orders_message') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection