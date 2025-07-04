@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.Edit_Type') }}: {{ $providerType->name }}</h4>
                    <small class="text-muted">{{ __('messages.Provider') }}: {{ $provider->name_of_manager }}</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.providerDetails.update', [$provider->id, $providerType->id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Type Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('messages.Type_Information') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="type_id" class="form-label">{{ __('messages.Type') }}</label>
                                    <select class="form-control @error('type_id') is-invalid @enderror" 
                                            id="type_id" name="type_id" required onchange="handleTypeChange()">
                                        <option value="">{{ __('messages.Select_Type') }}</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->id }}" 
                                                    data-booking-type="{{ $type->booking_type ?? 'hourly' }}"
                                                    {{ old('type_id', $providerType->type_id) == $type->id ? 'selected' : '' }}>
                                                {{ app()->getLocale() == 'ar' ? $type->name_ar : $type->name_en }}
                                                @if(isset($type->booking_type))
                                                    ({{ $type->booking_type == 'hourly' ? __('messages.Hourly') : __('messages.Service_Based') }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <!-- Hourly Services (checkbox style) -->
                                <div class="mb-3" id="hourly-services" style="display: none;">
                                    <label for="service_ids" class="form-label">{{ __('messages.Services') }}</label>
                                    <div class="border p-3 rounded @error('service_ids') border-danger @enderror" style="max-height: 200px; overflow-y: auto;">
                                        <div class="mb-2 border-bottom pb-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="selectAllServices()">
                                                {{ __('messages.Select_All') }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllServices()">
                                                {{ __('messages.Deselect_All') }}
                                            </button>
                                        </div>
                                        @foreach($services as $service)
                                            <div class="form-check">
                                                <input class="form-check-input hourly-service-checkbox" type="checkbox" 
                                                       name="service_ids[]" value="{{ $service->id }}" 
                                                       id="service_{{ $service->id }}" 
                                                       {{ in_array($service->id, old('service_ids', $selectedServiceIds)) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="service_{{ $service->id }}">
                                                    {{ app()->getLocale() == 'ar' ? $service->name_ar : $service->name_en }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-text">{{ __('messages.Select_Multiple_Services') }}</div>
                                    @error('service_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <!-- Service-based Services (with pricing) -->
                                <div class="mb-3" id="service-based-services" style="display: none;">
                                    <label class="form-label">{{ __('messages.Services_with_Pricing') }}</label>
                                    <div class="border p-3 rounded @error('service_prices') border-danger @enderror" style="max-height: 300px; overflow-y: auto;">
                                        <div class="mb-2 text-info">
                                            <small><i class="fas fa-info-circle"></i> {{ __('messages.Enter_Price_For_Each_Service') }}</small>
                                        </div>
                                        @foreach($services as $service)
                                            <div class="row mb-2 align-items-center border-bottom pb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label mb-0 fw-bold">
                                                        {{ app()->getLocale() == 'ar' ? $service->name_ar : $service->name_en }}
                                                    </label>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" 
                                                               class="form-control service-price-input" 
                                                               name="service_prices[{{ $service->id }}]" 
                                                               placeholder="{{ __('messages.Price') }}"
                                                               value="{{ old('service_prices.'.$service->id, isset($providerServices) ? ($providerServices[$service->id] ?? '') : '') }}"
                                                               step="0.01" 
                                                               min="0">
                                                        <span class="input-group-text">{{ __('messages.Currency') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-text">{{ __('messages.Leave_Empty_To_Exclude_Service') }}</div>
                                    @error('service_prices')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('messages.Type_Name') }}</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $providerType->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">{{ __('messages.Description') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4" required>{{ old('description', $providerType->description) }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3" id="hourly-price-field" style="display: none;">
                                    <label for="price_per_hour" class="form-label">{{ __('messages.Price_Per_Hour') }}</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('price_per_hour') is-invalid @enderror" 
                                               id="price_per_hour" name="price_per_hour" value="{{ old('price_per_hour', $providerType->price_per_hour) }}" 
                                               step="0.01" min="0">
                                        <span class="input-group-text">{{ __('messages.Currency') }}</span>
                                    </div>
                                    @error('price_per_hour')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <!-- Location & Settings -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('messages.Location_Settings') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="lat" class="form-label">{{ __('messages.Latitude') }}</label>
                                    <input type="number" class="form-control @error('lat') is-invalid @enderror" 
                                           id="lat" name="lat" value="{{ old('lat', $providerType->lat) }}" step="any" required>
                                    @error('lat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="lng" class="form-label">{{ __('messages.Longitude') }}</label>
                                    <input type="number" class="form-control @error('lng') is-invalid @enderror" 
                                           id="lng" name="lng" value="{{ old('lng', $providerType->lng) }}" step="any" required>
                                    @error('lng')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">{{ __('messages.Address') }}</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3">{{ old('address', $providerType->address) }}</textarea>
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="activate" class="form-label">{{ __('messages.Activate') }}</label>
                                    <select class="form-control @error('activate') is-invalid @enderror" 
                                            id="activate" name="activate" required>
                                        <option value="1" {{ old('activate', $providerType->activate) == '1' ? 'selected' : '' }}>{{ __('messages.Active') }}</option>
                                        <option value="2" {{ old('activate', $providerType->activate) == '2' ? 'selected' : '' }}>{{ __('messages.Inactive') }}</option>
                                    </select>
                                    @error('activate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">{{ __('messages.Status') }}</label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="1" {{ old('status', $providerType->status) == '1' ? 'selected' : '' }}>{{ __('messages.On') }}</option>
                                        <option value="2" {{ old('status', $providerType->status) == '2' ? 'selected' : '' }}>{{ __('messages.Off') }}</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="is_vip" class="form-label">{{ __('messages.VIP_Status') }}</label>
                                    <select class="form-control @error('is_vip') is-invalid @enderror" 
                                            id="is_vip" name="is_vip" required>
                                        <option value="1" {{ old('is_vip', $providerType->is_vip) == '1' ? 'selected' : '' }}>{{ __('messages.VIP') }}</option>
                                        <option value="2" {{ old('is_vip', $providerType->is_vip) == '2' ? 'selected' : '' }}>{{ __('messages.Regular') }}</option>
                                    </select>
                                    @error('is_vip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Current Images -->
                        @if($providerType->images->count() > 0)
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">{{ __('messages.Current_Type_Images') }}</h5>
                                    <div class="row">
                                        @foreach($providerType->images as $image)
                                            <div class="col-md-3 mb-3" id="image-{{ $image->id }}">
                                                <div class="card">
                                                    <img src="{{ $image->photo_url }}" 
                                                         class="card-img-top" style="height: 150px; object-fit: cover;">
                                                    <div class="card-body p-2">
                                                        <button type="button" class="btn btn-danger btn-sm w-100" 
                                                                onclick="deleteImage({{ $image->id }})">
                                                            {{ __('messages.Delete') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Current Galleries -->
                        @if($providerType->galleries->count() > 0)
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">{{ __('messages.Current_Gallery_Images') }}</h5>
                                    <div class="row">
                                        @foreach($providerType->galleries as $gallery)
                                            <div class="col-md-3 mb-3" id="gallery-{{ $gallery->id }}">
                                                <div class="card">
                                                    <img src="{{ $gallery->photo_url }}" 
                                                         class="card-img-top" style="height: 150px; object-fit: cover;">
                                                    <div class="card-body p-2">
                                                        <button type="button" class="btn btn-danger btn-sm w-100" 
                                                                onclick="deleteGallery({{ $gallery->id }})">
                                                            {{ __('messages.Delete') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Add New Images Section -->
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('messages.Add_Type_Images') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="images" class="form-label">{{ __('messages.Upload_Type_Images') }}</label>
                                    <input type="file" class="form-control @error('images.*') is-invalid @enderror" 
                                           id="images" name="images[]" multiple accept="image/*">
                                    <div class="form-text">{{ __('messages.Multiple_Images_Allowed') }}</div>
                                    @error('images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('messages.Add_Gallery_Images') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="galleries" class="form-label">{{ __('messages.Upload_Gallery_Images') }}</label>
                                    <input type="file" class="form-control @error('galleries.*') is-invalid @enderror" 
                                           id="galleries" name="galleries[]" multiple accept="image/*">
                                    <div class="form-text">{{ __('messages.Multiple_Images_Allowed') }}</div>
                                    @error('galleries.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ __('messages.Update') }}</button>
                            <a href="{{ route('admin.providerDetails.index', $provider->id) }}" class="btn btn-secondary">{{ __('messages.Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function handleTypeChange() {
    const typeSelect = document.getElementById('type_id');
    const selectedOption = typeSelect.options[typeSelect.selectedIndex];
    const bookingType = selectedOption.dataset.bookingType || 'hourly';
    
    const hourlyServices = document.getElementById('hourly-services');
    const serviceBasedServices = document.getElementById('service-based-services');
    const hourlyPriceField = document.getElementById('hourly-price-field');
    
    if (bookingType === 'hourly') {
        // Show hourly booking elements
        hourlyServices.style.display = 'block';
        hourlyPriceField.style.display = 'block';
        serviceBasedServices.style.display = 'none';
        
        // Make hourly price required
        document.getElementById('price_per_hour').required = true;
        
        // Clear service-based inputs
        document.querySelectorAll('.service-price-input').forEach(input => {
            input.value = '';
            input.required = false;
        });
        
    } else if (bookingType === 'service') {
        // Show service-based booking elements
        serviceBasedServices.style.display = 'block';
        hourlyServices.style.display = 'none';
        hourlyPriceField.style.display = 'none';
        
        // Make hourly price not required
        document.getElementById('price_per_hour').required = false;
        document.getElementById('price_per_hour').value = '';
        
        // Clear hourly service checkboxes
        document.querySelectorAll('.hourly-service-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
    } else {
        // Hide all service-related elements
        hourlyServices.style.display = 'none';
        serviceBasedServices.style.display = 'none';
        hourlyPriceField.style.display = 'none';
        
        // Clear all inputs
        document.getElementById('price_per_hour').required = false;
        document.getElementById('price_per_hour').value = '';
        document.querySelectorAll('.service-price-input').forEach(input => {
            input.value = '';
            input.required = false;
        });
        document.querySelectorAll('.hourly-service-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
    }
}

function selectAllServices() {
    const checkboxes = document.querySelectorAll('input[name="service_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function deselectAllServices() {
    const checkboxes = document.querySelectorAll('input[name="service_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}

function deleteImage(imageId) {
    if (confirm('{{ __("messages.Confirm_Delete_Image") }}')) {
        fetch('{{ route("admin.providerDetails.deleteImage", ":imageId") }}'.replace(':imageId', imageId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`image-${imageId}`).remove();
            } else {
                alert('{{ __("messages.Error_Deleting_Image") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("messages.Error_Deleting_Image") }}');
        });
    }
}

function deleteGallery(galleryId) {
    if (confirm('{{ __("messages.Confirm_Delete_Image") }}')) {
        fetch('{{ route("admin.providerDetails.deleteGallery", ":galleryId") }}'.replace(':galleryId', galleryId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`gallery-${galleryId}`).remove();
            } else {
                alert('{{ __("messages.Error_Deleting_Image") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("messages.Error_Deleting_Image") }}');
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    handleTypeChange();
});
</script>
@endsection