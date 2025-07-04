@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.Add_Type') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('types.store') }}" method="POST" enctype='multipart/form-data'>
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name_en" class="form-label">
                                {{ __('messages.Name_English') }}
                            </label>
                            <input type="text" 
                                   class="form-control @error('name_en') is-invalid @enderror" 
                                   id="name_en" 
                                   name="name_en" 
                                   value="{{ old('name_en') }}" 
                                   placeholder="{{ __('messages.Enter_English_Name') }}"
                                   required>
                            @error('name_en')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name_ar" class="form-label">
                                {{ __('messages.Name_Arabic') }}
                            </label>
                            <input type="text" 
                                   class="form-control @error('name_ar') is-invalid @enderror" 
                                   id="name_ar" 
                                   name="name_ar" 
                                   value="{{ old('name_ar') }}" 
                                   placeholder="{{ __('messages.Enter_Arabic_Name') }}"
                                   required>
                            @error('name_ar')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="minimum_order" class="form-label">
                                {{ __('messages.minimum_order') }}
                            </label>
                            <input type="text" 
                                   class="form-control @error('minimum_order') is-invalid @enderror" 
                                   id="minimum_order" 
                                   name="minimum_order" 
                                   value="{{ old('minimum_order') }}" 
                                   placeholder="{{ __('messages.minimum_order') }}"
                                   required>
                            @error('minimum_order')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="booking_type" class="form-label">
                                {{ __('messages.Booking_Type') }}
                            </label>
                            <select class="form-control @error('booking_type') is-invalid @enderror" 
                                    id="booking_type" 
                                    name="booking_type" 
                                    required>
                                <option value="">{{ __('messages.Select_Booking_Type') }}</option>
                                <option value="hourly" {{ old('booking_type') == 'hourly' ? 'selected' : '' }}>
                                    {{ __('messages.Hourly_Booking') }}
                                </option>
                                <option value="service" {{ old('booking_type') == 'service' ? 'selected' : '' }}>
                                    {{ __('messages.Service_Booking') }}
                                </option>
                            </select>
                            @error('booking_type')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="have_delivery" class="form-label">
                                {{ __('messages.have_delivery') }}
                            </label>
                            <select class="form-control @error('have_delivery') is-invalid @enderror" 
                                    id="have_delivery" 
                                    name="have_delivery" 
                                    required>
                                <option value="">{{ __('messages.Select_have_delivery') }}</option>
                                <option value="1" {{ old('have_delivery') == 1 ? 'selected' : '' }}>
                                    {{ __('messages.Yes') }}
                                </option>
                                <option value="2" {{ old('have_delivery') == 2 ? 'selected' : '' }}>
                                    {{ __('messages.No') }}
                                </option>
                            </select>
                            @error('have_delivery')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <img src="" id="image-preview" alt="Selected Image" height="50px" width="50px"
                                    style="display: none;">
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('Item_img').click()">
                                    {{ __('messages.Choose_Photo') }}
                                </button>
                                <input type="file" id="Item_img" name="photo" class="form-control" style="display: none;"
                                    onchange="previewImage()">
                                @error('photo')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                {{ __('messages.Save') }}
                            </button>
                            <a href="{{ route('types.index') }}" class="btn btn-secondary">
                                {{ __('messages.Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function previewImage() {
        var preview = document.getElementById('image-preview');
        var input = document.getElementById('Item_img');
        var file = input.files[0];
        if (file) {
            preview.style.display = "block";
            var reader = new FileReader();
            reader.onload = function() {
                preview.src = reader.result;
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = "none";
        }
    }
</script>
@endsection