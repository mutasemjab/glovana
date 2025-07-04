@extends('layouts.admin')

@section('title', __('messages.appointment_details'))

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('messages.appointment_details') }} #{{ $appointment->number }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">{{ __('messages.appointments') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('messages.appointment_details') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Appointment Information -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.appointment_information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.appointment_number') }}</label>
                                <p class="form-control-static"><strong>#{{ $appointment->number }}</strong></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.appointment_date') }}</label>
                                <p class="form-control-static">{{ $appointment->date->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.booking_type') }}</label>
                                <p class="form-control-static">
                                    <span class="badge {{ $appointment->booking_type == 'service' ? 'bg-primary' : 'bg-secondary' }} fs-6">
                                        {{ $appointment->booking_type == 'service' ? __('messages.service_based') : __('messages.hourly') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.appointment_status') }}</label>
                                <p class="form-control-static">
                                    <span class="badge bg-{{ 
                                        $appointment->appointment_status == 1 ? 'warning' : 
                                        ($appointment->appointment_status == 2 ? 'info' : 
                                        ($appointment->appointment_status == 3 ? 'primary' : 
                                        ($appointment->appointment_status == 4 ? 'success' : 'danger'))) 
                                    }} fs-6">
                                        {{ $appointment->appointment_status_label }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.payment_status') }}</label>
                                <p class="form-control-static">
                                    <span class="badge {{ $appointment->payment_status == 1 ? 'bg-success' : 'bg-warning' }} fs-6">
                                        {{ $appointment->payment_status_label }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.payment_type') }}</label>
                                <p class="form-control-static">
                                    <span class="badge bg-info fs-6">{{ ucfirst($appointment->payment_type) }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.vip_status') }}</label>
                                <p class="form-control-static">
                                    <span class="badge {{ $appointment->providerType->is_vip == 1 ? 'bg-warning' : 'bg-secondary' }} fs-6">
                                        {{ $appointment->is_vip_label }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.total_customers') }}</label>
                                <p class="form-control-static">
                                    <span class="badge bg-dark fs-6">{{ $appointment->total_customers }}</span>
                                </p>
                            </div>
                        </div>
                        @if($appointment->note)
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.customer_note') }}</label>
                                <p class="form-control-static">{{ $appointment->note }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Services Details (for service-based appointments) -->
            @if($appointment->booking_type == 'service' && $appointment->services_summary['services']->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.services_details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('messages.service_name') }}</th>
                                    <th>{{ __('messages.customer_count') }}</th>
                                    <th>{{ __('messages.service_price') }}</th>
                                    <th>{{ __('messages.total_price') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($appointment->services_summary['services'] as $service)
                                <tr>
                                    <td>{{ $service['name'] }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $service['customer_count'] }}</span>
                                    </td>
                                    <td>{{ number_format($service['service_price'], 2) }} {{ __('messages.jd') }}</td>
                                    <td>{{ number_format($service['total_price'], 2) }} {{ __('messages.jd') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-active">
                                <tr>
                                    <th>{{ __('messages.total') }}</th>
                                    <th>
                                        <span class="badge bg-dark">{{ $appointment->services_summary['total_customers'] }}</span>
                                    </th>
                                    <th>-</th>
                                    <th>{{ number_format($appointment->services_summary['services_total'], 2) }} {{ __('messages.jd') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Provider Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.provider_information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.provider_name') }}</label>
                                <p class="form-control-static">{{ $appointment->providerType->provider->name_of_manager ?? __('messages.no_provider') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.provider_phone') }}</label>
                                <p class="form-control-static">{{ $appointment->providerType->provider->phone ?? __('messages.no_phone') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.service_type') }}</label>
                                <p class="form-control-static">{{ $appointment->providerType->type->name ?? __('messages.no_type') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.service_name') }}</label>
                                <p class="form-control-static">{{ $appointment->providerType->name }}</p>
                            </div>
                        </div>
                        @if($appointment->booking_type == 'hourly')
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.price_per_hour') }}</label>
                                <p class="form-control-static">{{ number_format($appointment->providerType->price_per_hour, 2) }} {{ __('messages.jd') }}</p>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.provider_email') }}</label>
                                <p class="form-control-static">{{ $appointment->providerType->provider->email ?? __('messages.no_email') }}</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.service_description') }}</label>
                                <p class="form-control-static">{{ $appointment->providerType->description }}</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.provider_address') }}</label>
                                <p class="form-control-static">{{ $appointment->providerType->address }}</p>
                            </div>
                        </div>
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
                    @if($appointment->user)
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.customer_name') }}</label>
                            <p class="form-control-static">{{ $appointment->user->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.phone_number') }}</label>
                            <p class="form-control-static">{{ $appointment->user->country_code }}{{ $appointment->user->phone }}</p>
                        </div>
                        @if($appointment->user->email)
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.email') }}</label>
                            <p class="form-control-static">{{ $appointment->user->email }}</p>
                        </div>
                        @endif
                    @else
                        <p class="text-muted">{{ __('messages.no_customer_data') }}</p>
                    @endif
                </div>
            </div>

            <!-- Service Address -->
            @if($appointment->address)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.service_address') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.address') }}</label>
                        <p class="form-control-static">{{ $appointment->address->address }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.city') }}</label>
                        <p class="form-control-static">{{ $appointment->address->city }}</p>
                    </div>
                    @if($appointment->address->state)
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.state') }}</label>
                        <p class="form-control-static">{{ $appointment->address->state }}</p>
                    </div>
                    @endif
                    @if($appointment->address->lat && $appointment->address->lng)
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.coordinates') }}</label>
                        <p class="form-control-static">{{ $appointment->address->lat }}, {{ $appointment->address->lng }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Appointment Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.appointment_summary') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                @if($appointment->booking_type == 'service')
                                    <tr>
                                        <td>{{ __('messages.services_price') }}:</td>
                                        <td class="text-end">
                                            {{ number_format($appointment->services_summary['services_total'], 2) }} 
                                            {{ __('messages.jd') }}
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td>{{ __('messages.service_price') }}:</td>
                                        <td class="text-end">
                                            {{ number_format($appointment->total_prices - $appointment->delivery_fee, 2) }} 
                                            {{ __('messages.jd') }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>{{ __('messages.delivery_fee') }}:</td>
                                    <td class="text-end">{{ number_format($appointment->delivery_fee, 2) }} {{ __('messages.jd') }}</td>
                                </tr>
                                @if($appointment->total_discounts > 0)
                                <tr>
                                    <td>{{ __('messages.total_discounts') }}:</td>
                                    <td class="text-end text-success">-{{ number_format($appointment->total_discounts, 2) }} {{ __('messages.jd') }}</td>
                                </tr>
                                @endif
                                @if($appointment->coupon_discount > 0)
                                <tr>
                                    <td>{{ __('messages.coupon_discount') }}:</td>
                                    <td class="text-end text-success">-{{ number_format($appointment->coupon_discount, 2) }} {{ __('messages.jd') }}</td>
                                </tr>
                                @endif
                                <tr class="table-active">
                                    <td><strong>{{ __('messages.total_amount') }}:</strong></td>
                                    <td class="text-end"><strong>{{ number_format($appointment->total_prices, 2) }} {{ __('messages.jd') }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if($appointment->booking_type == 'service')
                    <hr>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <h6 class="mb-1">{{ $appointment->services_summary['total_services'] }}</h6>
                                <small class="text-muted">{{ __('messages.services') }}</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <h6 class="mb-1">{{ $appointment->services_summary['total_customers'] }}</h6>
                                <small class="text-muted">{{ __('messages.customers') }}</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <h6 class="mb-1">{{ number_format($appointment->services_summary['services_total'], 0) }}</h6>
                                <small class="text-muted">{{ __('messages.jd') }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Commission Details -->
            @if(isset($appointment->commission_details))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.commission_details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td>{{ __('messages.commission_rate') }}:</td>
                                    <td class="text-end">{{ $appointment->commission_details['commission_percentage'] }}%</td>
                                </tr>
                                <tr>
                                    <td>{{ __('messages.commission_amount') }}:</td>
                                    <td class="text-end">{{ number_format($appointment->commission_details['commission_amount'], 2) }} {{ __('messages.jd') }}</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>{{ __('messages.provider_amount') }}:</strong></td>
                                    <td class="text-end"><strong>{{ number_format($appointment->commission_details['provider_amount'], 2) }} {{ __('messages.jd') }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.actions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        {{-- <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn btn-primary">
                            <i class="ri-edit-line"></i> {{ __('messages.edit_appointment') }}
                        </a> --}}
                        <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> {{ __('messages.back_to_appointments') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection