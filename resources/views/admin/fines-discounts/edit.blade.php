@extends('layouts.admin')

@section('title', __('messages.edit_appointment'))

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('messages.edit_appointment') }} #{{ $appointment->number }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.appointments.index') }}">{{ __('messages.appointments') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.appointments.show', $appointment->id) }}">{{ __('messages.appointment_details') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('messages.edit') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Edit Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('messages.appointment_information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('messages.appointment_status') }} <span class="text-danger">*</span></label>
                                    <select name="appointment_status" class="form-select @error('appointment_status') is-invalid @enderror" required>
                                        <option value="">{{ __('messages.select_status') }}</option>
                                        <option value="1" {{ $appointment->appointment_status == 1 ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                                        <option value="2" {{ $appointment->appointment_status == 2 ? 'selected' : '' }}>{{ __('messages.accepted') }}</option>
                                        <option value="3" {{ $appointment->appointment_status == 3 ? 'selected' : '' }}>{{ __('messages.on_the_way') }}</option>
                                        <option value="4" {{ $appointment->appointment_status == 4 ? 'selected' : '' }}>{{ __('messages.delivered') }}</option>
                                        <option value="5" {{ $appointment->appointment_status == 5 ? 'selected' : '' }}>{{ __('messages.canceled') }}</option>
                                    </select>
                                    @error('appointment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('messages.payment_status') }} <span class="text-danger">*</span></label>
                                    <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                                        <option value="">{{ __('messages.select_status') }}</option>
                                        <option value="1" {{ $appointment->payment_status == 1 ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
                                        <option value="2" {{ $appointment->payment_status == 2 ? 'selected' : '' }}>{{ __('messages.unpaid') }}</option>
                                    </select>
                                    @error('payment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('messages.appointment_date') }} <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="date" class="form-control @error('date') is-invalid @enderror" 
                                           value="{{ old('date', $appointment->date->format('Y-m-d\TH:i')) }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('messages.admin_note') }}</label>
                                    <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="4" placeholder="{{ __('messages.add_admin_note') }}">{{ old('note', $appointment->note) }}</textarea>
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
                                        <li>{{ __('messages.appointment_status_change_note') }}</li>
                                        <li>{{ __('messages.appointment_payment_status_note') }}</li>
                                        <li>{{ __('messages.appointment_cancel_note') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> {{ __('messages.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> {{ __('messages.update_appointment') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Provider Assignment -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('messages.provider_assignment') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.appointments.assign-provider', $appointment->id) }}" method="POST" id="assignProviderForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('messages.assign_new_provider') }}</label>
                                        <select name="provider_type_id" class="form-select" id="newProviderSelect">
                                            <option value="">{{ __('messages.select_provider') }}</option>
                                            @foreach($providerTypes as $providerType)
                                                <option value="{{ $providerType->id }}" 
                                                        {{ $appointment->provider_type_id == $providerType->id ? 'selected' : '' }}>
                                                    {{ $providerType->name }} - {{ number_format($providerType->price_per_hour, 2) }} {{ __('messages.jd') }}/{{ __('messages.hour') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-warning d-block w-100" id="assignProviderBtn" disabled>
                                            <i class="ri-user-settings-line"></i> {{ __('messages.assign_provider') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Appointment Summary (Read Only) -->
            <div class="col-lg-4">
                <!-- Current Status -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('messages.current_status') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.appointment_number') }}</label>
                            <p class="form-control-static"><strong>#{{ $appointment->number }}</strong></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.current_appointment_status') }}</label>
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
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.current_payment_status') }}</label>
                            <p class="form-control-static">
                                <span class="badge {{ $appointment->payment_status == 1 ? 'bg-success' : 'bg-warning' }} fs-6">
                                    {{ $appointment->payment_status_label }}
                                </span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.payment_type') }}</label>
                            <p class="form-control-static">
                                <span class="badge bg-info fs-6">{{ ucfirst($appointment->payment_type) }}</span>
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

                <!-- Current Provider -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('messages.current_provider') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.provider_name') }}</label>
                            <p class="form-control-static">{{ $appointment->providerType->provider->name_of_manager ?? __('messages.no_provider') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.service_name') }}</label>
                            <p class="form-control-static">{{ $appointment->providerType->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.price_per_hour') }}</label>
                            <p class="form-control-static">{{ number_format($appointment->providerType->price_per_hour, 2) }} {{ __('messages.jd') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('messages.vip_status') }}</label>
                            <p class="form-control-static">
                                <span class="badge {{ $appointment->providerType->is_vip == 1 ? 'bg-warning' : 'bg-secondary' }} fs-6">
                                    {{ $appointment->is_vip_label }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Appointment Summary -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('messages.appointment_summary') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm">
                                <tbody>
                                    <tr>
                                        <td>{{ __('messages.service_price') }}:</td>
                                        <td class="text-end">{{ number_format($appointment->total_prices - $appointment->delivery_fee, 2) }} {{ __('messages.jd') }}</td>
                                    </tr>
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
    const appointmentStatusSelect = document.querySelector('select[name="appointment_status"]');
    const paymentStatusSelect = document.querySelector('select[name="payment_status"]');
    const newProviderSelect = document.getElementById('newProviderSelect');
    const assignProviderBtn = document.getElementById('assignProviderBtn');
    
    if (appointmentStatusSelect) {
        appointmentStatusSelect.addEventListener('change', function() {
            if (this.value == '5') { // Canceled
                if (!confirm('{{ __("messages.appointment_cancel_confirmation") }}')) {
                    this.value = '{{ $appointment->appointment_status }}';
                }
            }
        });
    }
    
    if (paymentStatusSelect) {
        paymentStatusSelect.addEventListener('change', function() {
            if (this.value == '1' && '{{ $appointment->payment_type }}' === 'wallet') {
                if (!confirm('{{ __("messages.wallet_payment_confirmation") }}')) {
                    this.value = '{{ $appointment->payment_status }}';
                }
            }
        });
    }

    // Enable/disable assign provider button
    if (newProviderSelect && assignProviderBtn) {
        newProviderSelect.addEventListener('change', function() {
            if (this.value && this.value != '{{ $appointment->provider_type_id }}') {
                assignProviderBtn.disabled = false;
            } else {
                assignProviderBtn.disabled = true;
            }
        });

        // Confirm provider assignment
        document.getElementById('assignProviderForm').addEventListener('submit', function(e) {
            if (!confirm('{{ __("messages.assign_provider_confirmation") }}')) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endsection