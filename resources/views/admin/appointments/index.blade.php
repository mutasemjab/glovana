@extends('layouts.admin')

@section('title', __('messages.appointments'))

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('messages.appointments') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('messages.appointments') }}</li>
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
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('messages.total_appointments') }}</span>
                            <h4 class="mb-3">{{ number_format($statistics['total_appointments']) }}</h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title bg-primary rounded-circle">
                                    <i class="ri-calendar-line font-size-24"></i>
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
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('messages.pending_appointments') }}</span>
                            <h4 class="mb-3">{{ number_format($statistics['pending_appointments']) }}</h4>
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
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('messages.hourly_appointments') }}</span>
                            <h4 class="mb-3">{{ number_format($statistics['hourly_appointments']) }}</h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-info">
                                <span class="avatar-title bg-info rounded-circle">
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
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ __('messages.service_appointments') }}</span>
                            <h4 class="mb-3">{{ number_format($statistics['service_based_appointments']) }}</h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-success">
                                <span class="avatar-title bg-success rounded-circle">
                                    <i class="ri-service-line font-size-24"></i>
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
                    <form method="GET" action="{{ route('appointments.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.appointment_status') }}</label>
                                <select name="appointment_status" class="form-control">
                                    <option value="">{{ __('messages.all_statuses') }}</option>
                                    <option value="1" {{ request('appointment_status') == '1' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                                    <option value="2" {{ request('appointment_status') == '2' ? 'selected' : '' }}>{{ __('messages.accepted') }}</option>
                                    <option value="3" {{ request('appointment_status') == '3' ? 'selected' : '' }}>{{ __('messages.on_the_way') }}</option>
                                    <option value="4" {{ request('appointment_status') == '4' ? 'selected' : '' }}>{{ __('messages.delivered') }}</option>
                                    <option value="5" {{ request('appointment_status') == '5' ? 'selected' : '' }}>{{ __('messages.canceled') }}</option>
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
                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.booking_type') }}</label>
                                <select name="booking_type" class="form-control">
                                    <option value="">{{ __('messages.all_types') }}</option>
                                    <option value="hourly" {{ request('booking_type') == 'hourly' ? 'selected' : '' }}>{{ __('messages.hourly') }}</option>
                                    <option value="service" {{ request('booking_type') == 'service' ? 'selected' : '' }}>{{ __('messages.service_based') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.provider_type') }}</label>
                                <select name="provider_type_id" class="form-control">
                                    <option value="">{{ __('messages.all_providers') }}</option>
                                    @foreach($providerTypes as $providerType)
                                        <option value="{{ $providerType->id }}" {{ request('provider_type_id') == $providerType->id ? 'selected' : '' }}>
                                            {{ $providerType->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.vip_status') }}</label>
                                <select name="is_vip" class="form-control">
                                    <option value="">{{ __('messages.all_types') }}</option>
                                    <option value="1" {{ request('is_vip') == '1' ? 'selected' : '' }}>{{ __('messages.vip_only') }}</option>
                                    <option value="2" {{ request('is_vip') == '2' ? 'selected' : '' }}>{{ __('messages.regular_only') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.from_date') }}</label>
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.to_date') }}</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('messages.search') }}</label>
                                <input type="text" name="search" class="form-control" placeholder="{{ __('messages.search_appointments') }}" value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-search-line"></i> {{ __('messages.filter') }}
                                </button>
                                <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                                    <i class="ri-refresh-line"></i> {{ __('messages.reset') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('messages.appointments_list') }}</h4>
                </div>
                <div class="card-body">
                    @if($appointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('messages.appointment_number') }}</th>
                                        <th>{{ __('messages.customer') }}</th>
                                        <th>{{ __('messages.provider') }}</th>
                                        <th>{{ __('messages.booking_type') }}</th>
                                        <th>{{ __('messages.services_details') }}</th>
                                        <th>{{ __('messages.total_amount') }}</th>
                                        <th>{{ __('messages.appointment_status') }}</th>
                                        <th>{{ __('messages.payment_status') }}</th>
                                        <th>{{ __('messages.vip_status') }}</th>
                                        <th>{{ __('messages.appointment_date') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($appointments as $appointment)
                                        <tr>
                                            <td><strong>#{{ $appointment->number }}</strong></td>
                                            <td>
                                                <div>{{ $appointment->user->name ?? __('messages.no_customer') }}</div>
                                                <small class="text-muted">{{ $appointment->user->phone ?? '' }}</small>
                                            </td>
                                            <td>
                                                <div>{{ $appointment->providerType->provider->name_of_manager ?? __('messages.no_provider') }}</div>
                                                <small class="text-muted">{{ $appointment->providerType->name ?? '' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge {{ $appointment->booking_type == 'service' ? 'bg-primary' : 'bg-secondary' }}">
                                                    {{ $appointment->booking_type == 'service' ? __('messages.service_based') : __('messages.hourly') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($appointment->booking_type == 'service')
                                                    <div class="small">
                                                        <strong>{{ __('messages.services') }}:</strong> {{ $appointment->services_summary['total_services'] }}<br>
                                                        <strong>{{ __('messages.customers') }}:</strong> {{ $appointment->services_summary['total_customers'] }}<br>
                                                        @if($appointment->services_summary['services']->count() > 0)
                                                            <div class="mt-1">
                                                                @foreach($appointment->services_summary['services']->take(2) as $service)
                                                                    <span class="badge bg-light text-dark me-1">{{ $service['name'] }} ({{ $service['customer_count'] }})</span>
                                                                @endforeach
                                                                @if($appointment->services_summary['services']->count() > 2)
                                                                    <span class="text-muted">+{{ $appointment->services_summary['services']->count() - 2 }} {{ __('messages.more') }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="small text-muted">
                                                        {{ __('messages.hourly_booking') }}<br>
                                                        <strong>{{ __('messages.rate') }}:</strong> {{ number_format($appointment->providerType->price_per_hour, 2) }} {{ __('messages.jd') }}/{{ __('messages.hour') }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{ number_format($appointment->total_prices, 2) }} {{ __('messages.jd') }}</td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    $appointment->appointment_status == 1 ? 'warning' : 
                                                    ($appointment->appointment_status == 2 ? 'info' : 
                                                    ($appointment->appointment_status == 3 ? 'primary' : 
                                                    ($appointment->appointment_status == 4 ? 'success' : 'danger'))) 
                                                }}">
                                                    {{ $appointment->appointment_status_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $appointment->payment_status == 1 ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $appointment->payment_status_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $appointment->providerType->is_vip == 1 ? 'bg-warning' : 'bg-secondary' }}">
                                                    {{ $appointment->is_vip_label }}
                                                </span>
                                            </td>
                                            <td>{{ $appointment->date->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('appointments.show', $appointment->id) }}" class="btn btn-sm btn-info">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="ri-edit-line"></i>
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
                                {{ $appointments->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ri-calendar-line font-size-48 text-muted"></i>
                            <h5 class="mt-3">{{ __('messages.no_appointments_found') }}</h5>
                            <p class="text-muted">{{ __('messages.no_appointments_message') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection