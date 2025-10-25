@extends('layouts.admin')

@section('title', __('messages.order_details'))

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('messages.order_details') }} #{{ $order->number }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">{{ __('messages.orders') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('messages.order_details') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Information -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.order_information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.order_number') }}</label>
                                <p class="form-control-static"><strong>#{{ $order->number }}</strong></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.order_date') }}</label>
                                <p class="form-control-static">{{ $order->date->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.order_status') }}</label>
                                <p class="form-control-static">
                                    <span class="badge bg-{{ $order->status_color }} fs-6">
                                        {{ $order->order_status_label }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.payment_status') }}</label>
                                <p class="form-control-static">
                                    <span class="badge {{ $order->payment_status == 1 ? 'bg-success' : 'bg-warning' }} fs-6">
                                        {{ $order->payment_status_label }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.payment_type') }}</label>
                                <p class="form-control-static">
                                    <span class="badge bg-info fs-6">{{ ucfirst($order->payment_type) }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.total_items') }}</label>
                                <p class="form-control-static">{{ $order->total_items }} {{ __('messages.items') }}</p>
                            </div>
                        </div>
                        @if($order->note)
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.note') }}</label>
                                <p class="form-control-static">{{ $order->note }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Products -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.order_products') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('messages.product') }}</th>
                                    <th>{{ __('messages.unit_price') }}</th>
                                    <th>{{ __('messages.quantity') }}</th>
                                    <th>{{ __('messages.tax_percentage') }}</th>
                                    <th>{{ __('messages.discount') }}</th>
                                    <th>{{ __('messages.total_price') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderProducts as $orderProduct)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($orderProduct->product->image)
                                                <img src="{{ asset('storage/' . $orderProduct->product->image) }}" 
                                                     alt="{{ $orderProduct->product->name }}" 
                                                     class="rounded" 
                                                     width="50" height="50">
                                            @endif
                                            <div class="ms-3">
                                                <h6 class="mb-1">{{ $orderProduct->product->name }}</h6>
                                                @if($orderProduct->product->description)
                                                    <small class="text-muted">{{ Str::limit($orderProduct->product->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ number_format($orderProduct->unit_price, 2) }} {{ __('messages.jd') }}</td>
                                    <td>{{ $orderProduct->quantity }}</td>
                                    <td>{{ $orderProduct->tax_percentage }}%</td>
                                    <td>
                                        @if($orderProduct->discount_percentage)
                                            {{ $orderProduct->discount_percentage }}%
                                            ({{ number_format($orderProduct->discount_value, 2) }} {{ __('messages.jd') }})
                                        @else
                                            {{ __('messages.no_discount') }}
                                        @endif
                                    </td>
                                    <td><strong>{{ number_format($orderProduct->total_price_after_tax, 2) }} {{ __('messages.jd') }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Summary Information -->
        <div class="col-lg-4">
            <!-- Customer Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.customer_information') }}</h5>
                </div>
                <div class="card-body">
                    @if($order->user)
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.customer_name') }}</label>
                            <p class="form-control-static">{{ $order->user->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.phone_number') }}</label>
                            <p class="form-control-static">{{ $order->user->country_code }}{{ $order->user->phone }}</p>
                        </div>
                        @if($order->user->email)
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.email') }}</label>
                            <p class="form-control-static">{{ $order->user->email }}</p>
                        </div>
                        @endif
                    @else
                        <p class="text-muted">{{ __('messages.no_customer_data') }}</p>
                    @endif
                </div>
            </div>

            <!-- Delivery Address -->
            @if($order->address)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.delivery_address') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.address') }}</label>
                        <p class="form-control-static">{{ $order->address->address }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.city') }}</label>
                        <p class="form-control-static">{{ $order->address->city }}</p>
                    </div>
                    @if($order->address->state)
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.state') }}</label>
                        <p class="form-control-static">{{ $order->address->state }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.order_summary') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td>{{ __('messages.subtotal') }}:</td>
                                    <td class="text-end">
                                        {{ number_format($order->total_prices - $order->delivery_fee - $order->total_taxes + $order->total_discounts, 2) }} 
                                        {{ __('messages.jd') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ __('messages.delivery_fee') }}:</td>
                                    <td class="text-end">{{ number_format($order->delivery_fee, 2) }} {{ __('messages.jd') }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('messages.total_taxes') }}:</td>
                                    <td class="text-end">{{ number_format($order->total_taxes, 2) }} {{ __('messages.jd') }}</td>
                                </tr>
                                @if($order->total_discounts > 0)
                                <tr>
                                    <td>{{ __('messages.total_discounts') }}:</td>
                                    <td class="text-end text-success">-{{ number_format($order->total_discounts, 2) }} {{ __('messages.jd') }}</td>
                                </tr>
                                @endif
                                @if($order->coupon_discount > 0)
                                <tr>
                                    <td>{{ __('messages.coupon_discount') }}:</td>
                                    <td class="text-end text-success">-{{ number_format($order->coupon_discount, 2) }} {{ __('messages.jd') }}</td>
                                </tr>
                                @endif
                                <tr class="table-active">
                                    <td><strong>{{ __('messages.total_amount') }}:</strong></td>
                                    <td class="text-end"><strong>{{ number_format($order->total_prices, 2) }} {{ __('messages.jd') }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.actions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-primary">
                            <i class="ri-edit-line"></i> {{ __('messages.edit_order') }}
                        </a>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> {{ __('messages.back_to_orders') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection