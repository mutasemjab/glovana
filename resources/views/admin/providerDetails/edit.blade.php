@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.Edit_Service') }}: {{ $providerServiceType->name }}</h4>
                    <small class="text-muted">{{ __('messages.Provider') }}: {{ $provider->name_of_manager }}</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.providerDetails.update', [$provider->id, $providerServiceType->id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Service Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('messages.Service_Information') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="service_id" class="form-label">{{ __('messages.Service') }}</label>
                                    <select class="form-control @error('service_id') is-invalid @enderror" 
                                            id="service_id" name="service_id" required>
                                        <option value="">{{ __('messages.Select_Service') }}</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" 
                                                    {{ old('service_id', $providerServiceType->service_id) == $service->id ? 'selected' : '' }}>
                                                {{ app()->getLocale() == 'ar' ? $service->name_ar : $service->name_en }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="type_id" class="form-label">{{ __('messages.Type') }}</label>
                                    <select class="form-control @error('type_id') is-invalid @enderror" 
                                            id="type_id" name="type_id" required>
                                        <option value="">{{ __('messages.Select_Type') }}</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->id }}" 
                                                    {{ old('type_id', $providerServiceType->type_id) == $type->id ? 'selected' : '' }}>
                                                {{ app()->getLocale() == 'ar' ? $type->name_ar : $type->name_en }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('messages.Service_Name') }}</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $providerServiceType->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">{{ __('messages.Description') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4" required>{{ old('description', $providerServiceType->description) }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="price_per_hour" class="form-label">{{ __('messages.Price_Per_Hour') }}</label>
                                    <input type="number" class="form-control @error('price_per_hour') is-invalid @enderror" 
                                           id="price_per_hour" name="price_per_hour" value="{{ old('price_per_hour', $providerServiceType->price_per_hour) }}" 
                                           step="0.01" min="0" required>
                                    @error('price_per_hour')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <!-- Location & Settings -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('messages.Location_Settings') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="lat" class="form-label">{{ __('messages.Latitude') }}</label>
                                    <input type="number" class="form-control @error('lat') is-invalid @enderror" 
                                           id="lat" name="lat" value="{{ old('lat', $providerServiceType->lat) }}" step="any" required>
                                    @error('lat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="lng" class="form-label">{{ __('messages.Longitude') }}</label>
                                    <input type="number" class="form-control @error('lng') is-invalid @enderror" 
                                           id="lng" name="lng" value="{{ old('lng', $providerServiceType->lng) }}" step="any" required>
                                    @error('lng')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">{{ __('messages.Address') }}</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3">{{ old('address', $providerServiceType->address) }}</textarea>
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="activate" class="form-label">{{ __('messages.Activate') }}</label>
                                    <select class="form-control @error('activate') is-invalid @enderror" 
                                            id="activate" name="activate" required>
                                        <option value="1" {{ old('activate', $providerServiceType->activate) == '1' ? 'selected' : '' }}>{{ __('messages.Active') }}</option>
                                        <option value="2" {{ old('activate', $providerServiceType->activate) == '2' ? 'selected' : '' }}>{{ __('messages.Inactive') }}</option>
                                    </select>
                                    @error('activate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">{{ __('messages.Status') }}</label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="1" {{ old('status', $providerServiceType->status) == '1' ? 'selected' : '' }}>{{ __('messages.On') }}</option>
                                        <option value="2" {{ old('status', $providerServiceType->status) == '2' ? 'selected' : '' }}>{{ __('messages.Off') }}</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="is_vip" class="form-label">{{ __('messages.VIP_Status') }}</label>
                                    <select class="form-control @error('is_vip') is-invalid @enderror" 
                                            id="is_vip" name="is_vip" required>
                                        <option value="1" {{ old('is_vip', $providerServiceType->is_vip) == '1' ? 'selected' : '' }}>{{ __('messages.VIP') }}</option>
                                        <option value="2" {{ old('is_vip', $providerServiceType->is_vip) == '2' ? 'selected' : '' }}>{{ __('messages.Regular') }}</option>
                                    </select>
                                    @error('is_vip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Current Images -->
                        @if($providerServiceType->images->count() > 0)
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">{{ __('messages.Current_Service_Images') }}</h5>
                                    <div class="row">
                                        @foreach($providerServiceType->images as $image)
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
                        @if($providerServiceType->galleries->count() > 0)
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">{{ __('messages.Current_Gallery_Images') }}</h5>
                                    <div class="row">
                                        @foreach($providerServiceType->galleries as $gallery)
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
                                <h5 class="mb-3">{{ __('messages.Add_Service_Images') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="images" class="form-label">{{ __('messages.Upload_Service_Images') }}</label>
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
function deleteImage(imageId) {
    if (confirm('{{ __("messages.Confirm_Delete_Image") }}')) {
        fetch(`/admin/provider-details/images/${imageId}`, {
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
        fetch(`/admin/provider-details/galleries/${galleryId}`, {
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
</script>
@endsection