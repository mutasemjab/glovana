@extends('layouts.admin')

@section('title', __('messages.edit_order'))

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('messages.edit_order') }} #{{ $order->number }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">{{ __('messages.orders') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('orders.show', $order->id) }}">{{ __('messages.order_details') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('messages.edit') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('orders.update', $order->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Edit Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('messages.order_information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('messages.order_status') }} <span class="text-danger">*</span></label>
                                    <select name="order_status" class="form-control @error('order_status') is-invalid @enderror" required>
                                        <option value="">{{ __('messages.select_status') }}</option>
                                        <option value="1" {{ $order->order_status == 1 ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                                        <option value="2" {{ $order->order_status == 2 ? 'selected' : '' }}>{{ __('messages.accepted') }}</option>
                                        <option value="3" {{ $order->order_status == 3 ? 'selected' : '' }}>{{ __('messages.on_the_way') }}</option>
                                        <option value="4" {{ $order->order_status == 4 ? 'selected' : '' }}>{{ __('messages.delivered') }}</option>
                                        <option value="5" {{ $order->order_status == 5 ? 'selected' : '' }}>{{ __('messages.canceled') }}</option>
                                        <option value="6" {{ $order->order_status == 6 ? 'selected' : '' }}>{{ __('messages.refund') }}</option>
                                    </select>
                                    @error('order_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('messages.payment_status') }} <span class="text-danger">*</span></label>
                                    <select name="payment_status" class="form-control @error('payment_status') is-invalid @enderror" required>
                                        <option value="">{{ __('messages.select_status') }}</option>
                                        <option value="1" {{ $order->payment_status == 1 ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
                                        <option value="2" {{ $order->payment_status == 2 ? 'selected' : '' }}>{{ __('messages.unpaid') }}</option>
                                    </select>
                                    @error('payment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('messages.admin_note') }}</label>
                                    <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="4" placeholder="{{ __('messages.add_admin_note') }}">{{ old('note', $order->note) }}</textarea>
                                    @error('note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">{{ __('messages.admin_note_help') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i>
                                    <strong>{{ __('messages.important_notes') }}:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>{{ __('messages.status_change_note') }}</li>
                                        <li>{{ __('messages.payment_status_note') }}</li>
                                        <li>{{ __('messages.refund_note') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> {{ __('messages.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> {{ __('messages.update_order') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary (Read Only) -->
            <div class="col-lg-4">
                <!-- Current Status -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('messages.current_status') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.order_number') }}</label>
                            <p class="form-control-static"><strong>#{{ $order->number }}</strong></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.current_order_status') }}</label>
                            <p class="form-control-static">
                                <span class="badge bg-{{ $order->status_color }} fs-6">
                                    {{ $order->order_status_label }}
                                </span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.current_payment_status') }}</label>
                            <p class="form-control-static">
                                <span class="badge {{ $order->payment_status == 1 ? 'bg-success' : 'bg-warning' }} fs-6">
                                    {{ $order->payment_status_label }}
                                </span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.payment_type') }}</label>
                            <p class="form-control-static">
                                <span class="badge bg-info fs-6">{{ ucfirst($order->payment_type) }}</span>
                            </p>
                        </div>
                    </div>
                </div>

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

                <!-- Order Summary -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('messages.order_summary') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm">
                                <tbody>
                                    <tr>
                                        <td>{{ __('messages.total_items') }}:</td>
                                        <td class="text-end">{{ $order->total_items }}</td>
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
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show confirmation for status changes
    const orderStatusSelect = document.querySelector('select[name="order_status"]');
    const paymentStatusSelect = document.querySelector('select[name="payment_status"]');
    
    if (orderStatusSelect) {
        orderStatusSelect.addEventListener('change', function() {
            if (this.value == '6') { // Refund
                if (!confirm('{{ __("messages.refund_confirmation") }}')) {
                    this.value = '{{ $order->order_status }}';
                }
            } else if (this.value == '5') { // Canceled
                if (!confirm('{{ __("messages.cancel_confirmation") }}')) {
                    this.value = '{{ $order->order_status }}';
                }
            }
        });
    }
    
    if (paymentStatusSelect) {
        paymentStatusSelect.addEventListener('change', function() {
            if (this.value == '1' && '{{ $order->payment_type }}' === 'wallet') {
                if (!confirm('{{ __("messages.wallet_payment_confirmation") }}')) {
                    this.value = '{{ $order->payment_status }}';
                }
            }
        });
    }
});
</script>
@endsection