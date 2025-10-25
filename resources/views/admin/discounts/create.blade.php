@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.Create_Discount') }}</h4>
                    <small class="text-muted">{{ __('messages.Provider_Type') }}: {{ $providerType->name }} | {{ __('messages.Provider') }}: {{ $providerType->provider->name_of_manager }}</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('discounts.store', [$providerId, $providerType->id]) }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('messages.Basic_Information') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('messages.Discount_Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">{{ __('messages.Description') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="percentage" class="form-label">{{ __('messages.Discount_Percentage') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('percentage') is-invalid @enderror" 
                                               id="percentage" name="percentage" value="{{ old('percentage') }}" 
                                               min="0" max="100" step="0.01" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    @error('percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text">{{ __('messages.Enter_Percentage_0_to_100') }}</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">{{ __('messages.Start_Date') }} <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                                   id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">{{ __('messages.End_Date') }} <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                                   id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Discount Settings -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('messages.Discount_Settings') }}</h5>
                                
                                <!-- Hidden input for discount type (automatically determined) -->
                                <input type="hidden" id="discount_type" name="discount_type" value="{{ old('discount_type', $providerType->type->booking_type) }}">
                                
                                <div class="mb-3">
                                    <label class="form-label">{{ __('messages.Discount_Type') }}</label>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>{{ __('messages.Auto_Determined_Type') }}:</strong>
                                        @if($providerType->type->booking_type == 'hourly')
                                            <span class="badge badge-primary">{{ __('messages.Hourly_Pricing_Only') }}</span>
                                            <br><small>{{ __('messages.Applied_to_hourly_rate') }}</small>
                                        @elseif($providerType->type->booking_type == 'service')
                                            <span class="badge badge-success">{{ __('messages.Service_Pricing_Only') }}</span>
                                            <br><small>{{ __('messages.Applied_to_individual_services') }}</small>
                                        @else
                                            <span class="badge badge-warning">{{ __('messages.Both_Hourly_and_Service') }}</span>
                                            <br><small>{{ __('messages.Applied_to_both_pricing_types') }}</small>
                                        @endif
                                    </div>
                                    <div class="form-text">
                                        {{ __('messages.Discount_type_automatically_determined') }}
                                    </div>
                                </div>

                                <!-- Service Selection -->
                                <div class="mb-3" id="service-selection" style="{{ $providerType->type->booking_type == 'service' ? 'display: block;' : 'display: none;' }}">
                                    <label class="form-label">{{ __('messages.Apply_to_Services') }}</label>
                                    <div class="border p-3 rounded @error('service_ids') border-danger @enderror" style="max-height: 250px; overflow-y: auto;">
                                        <div class="mb-2 border-bottom pb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="all_services" onchange="toggleAllServices()">
                                                <label class="form-check-label fw-bold" for="all_services">
                                                    {{ __('messages.Apply_to_All_Services') }}
                                                </label>
                                            </div>
                                            <div class="form-text">{{ __('messages.Leave_unchecked_for_specific_services') }}</div>
                                        </div>
                                        
                                        <div id="specific-services">
                                            @foreach($services as $service)
                                                <div class="form-check">
                                                    <input class="form-check-input service-checkbox" type="checkbox" 
                                                           name="service_ids[]" value="{{ $service->id }}" 
                                                           id="service_{{ $service->id }}" 
                                                           {{ in_array($service->id, old('service_ids', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="service_{{ $service->id }}">
                                                        {{ app()->getLocale() == 'ar' ? $service->name_ar : $service->name_en }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @error('service_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    <div class="form-text">
                                        {{ __('messages.If_no_services_selected_applies_to_all') }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="is_active" class="form-label">{{ __('messages.Status') }} <span class="text-danger">*</span></label>
                                    <select class="form-control @error('is_active') is-invalid @enderror" 
                                            id="is_active" name="is_active" required>
                                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>
                                            {{ __('messages.Active') }}
                                        </option>
                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>
                                            {{ __('messages.Inactive') }}
                                        </option>
                                    </select>
                                    @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> {{ __('messages.Discount_Preview') }}</h6>
                                    <div id="discount-preview">
                                        {{ __('messages.Select_discount_type_to_see_preview') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">{{ __('messages.Create_Discount') }}</button>
                                <a href="{{ route('discounts.index', [$providerId, $providerType->id]) }}" class="btn btn-secondary">{{ __('messages.Cancel') }}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize based on provider type booking_type
const providerBookingType = '{{ $providerType->type->booking_type }}';

function handleDiscountTypeChange() {
    // This function is now simplified since type is auto-determined
    updatePreview();
}

function toggleAllServices() {
    const allServicesCheckbox = document.getElementById('all_services');
    const specificServices = document.getElementById('specific-services');
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    
    if (allServicesCheckbox.checked) {
        specificServices.style.display = 'none';
        serviceCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    } else {
        specificServices.style.display = 'block';
    }
    
    updatePreview();
}

function updatePreview() {
    const discountType = providerBookingType; // Use provider type's booking_type
    const percentage = document.getElementById('percentage').value;
    const serviceSelection = document.getElementById('service-selection');
    const previewDiv = document.getElementById('discount-preview');
    
    // Show/hide service selection based on provider type
    if (discountType === 'service') {
        serviceSelection.style.display = 'block';
    } else {
        serviceSelection.style.display = 'none';
    }
    
    if (!percentage) {
        previewDiv.innerHTML = '{{ __('messages.Enter_percentage_to_see_preview') }}';
        return;
    }
    
    let preview = `<strong>${percentage}%</strong> {{ __('messages.discount_will_be_applied_to') }}: `;
    
    if (discountType === 'hourly') {
        preview += '{{ __('messages.Hourly_pricing_only') }}';
    } else if (discountType === 'service') {
        const allServices = document.getElementById('all_services') ? document.getElementById('all_services').checked : true;
        const selectedServices = document.querySelectorAll('.service-checkbox:checked').length;
        
        if (allServices || selectedServices === 0) {
            preview += '{{ __('messages.All_services') }}';
        } else {
            preview += `{{ __('messages.Selected_services') }} (${selectedServices})`;
        }
    } else {
        preview += '{{ __('messages.Both_hourly_and_all_services') }}';
    }
    
    previewDiv.innerHTML = preview;
}

// Event listeners
document.getElementById('percentage').addEventListener('input', updatePreview);

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePreview();
});
</script>
@endsection