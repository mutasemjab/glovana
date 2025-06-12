@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.Edit_Product') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('messages.Basic_Information') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="name_en" class="form-label">{{ __('messages.Name_English') }}</label>
                                    <input type="text" class="form-control @error('name_en') is-invalid @enderror" 
                                           id="name_en" name="name_en" value="{{ old('name_en', $product->name_en) }}" required>
                                    @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="name_ar" class="form-label">{{ __('messages.Name_Arabic') }}</label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" name="name_ar" value="{{ old('name_ar', $product->name_ar) }}" required>
                                    @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="category_id" class="form-label">{{ __('messages.Category') }}</label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" 
                                            id="category_id" name="category_id">
                                        <option value="">{{ __('messages.Select_Category') }}</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ app()->getLocale() == 'ar' ? $category->name_ar : $category->name_en }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="sold" class="form-label">{{ __('messages.Sold_Status') }}</label>
                                    <input type="text" class="form-control @error('sold') is-invalid @enderror" 
                                           id="sold" name="sold" value="{{ old('sold', $product->sold) }}" 
                                           placeholder="{{ __('messages.Enter_Sold_Status') }}">
                                    @error('sold')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <!-- Pricing -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('messages.Pricing') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="price" class="form-label">{{ __('messages.Price') }}</label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                           id="price" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="tax" class="form-label">{{ __('messages.Tax') }}</label>
                                    <input type="number" class="form-control @error('tax') is-invalid @enderror" 
                                           id="tax" name="tax" value="{{ old('tax', $product->tax) }}" step="0.01" min="0" required>
                                    @error('tax')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="discount_percentage" class="form-label">{{ __('messages.Discount_Percentage') }}</label>
                                    <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror" 
                                           id="discount_percentage" name="discount_percentage" 
                                           value="{{ old('discount_percentage', $product->discount_percentage) }}" 
                                           step="0.01" min="0" max="100" placeholder="0">
                                    @error('discount_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                @if($product->price_after_discount && $product->price_after_discount != $product->price)
                                    <div class="alert alert-info">
                                        <strong>{{ __('messages.Current_Price_After_Discount') }}:</strong> 
                                        {{ number_format($product->price_after_discount, 2) }} {{ __('messages.Currency') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Descriptions -->
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('messages.Description') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="description_en" class="form-label">{{ __('messages.Description_English') }}</label>
                                    <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                              id="description_en" name="description_en" rows="4" required>{{ old('description_en', $product->description_en) }}</textarea>
                                    @error('description_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="specification_en" class="form-label">{{ __('messages.Specification_English') }}</label>
                                    <textarea class="form-control @error('specification_en') is-invalid @enderror" 
                                              id="specification_en" name="specification_en" rows="3">{{ old('specification_en', $product->specification_en) }}</textarea>
                                    @error('specification_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3">&nbsp;</h5>
                                
                                <div class="mb-3">
                                    <label for="description_ar" class="form-label">{{ __('messages.Description_Arabic') }}</label>
                                    <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                              id="description_ar" name="description_ar" rows="4" required>{{ old('description_ar', $product->description_ar) }}</textarea>
                                    @error('description_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="specification_ar" class="form-label">{{ __('messages.Specification_Arabic') }}</label>
                                    <textarea class="form-control @error('specification_ar') is-invalid @enderror" 
                                              id="specification_ar" name="specification_ar" rows="3">{{ old('specification_ar', $product->specification_ar) }}</textarea>
                                    @error('specification_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Current Images -->
                        @if($productImages->count() > 0)
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">{{ __('messages.Current_Images') }}</h5>
                                    <div class="row">
                                        @foreach($productImages as $image)
                                            <div class="col-md-3 mb-3" id="image-{{ $image->id }}">
                                                <div class="card">
                                                    <img src="{{ asset('assets/admin/uploads/' . $image->photo) }}" 
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

                        <!-- Add New Images -->
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="mb-3">{{ __('messages.Add_New_Images') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="images" class="form-label">{{ __('messages.Upload_Images') }}</label>
                                    <input type="file" class="form-control @error('images.*') is-invalid @enderror" 
                                           id="images" name="images[]" multiple accept="image/*">
                                    <div class="form-text">{{ __('messages.Multiple_Images_Allowed') }}</div>
                                    @error('images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ __('messages.Update') }}</button>
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">{{ __('messages.Cancel') }}</a>
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
    function deleteImage(imageId) {
    if (confirm('{{ __("messages.Confirm_Delete_Image") }}')) {
        fetch(`{{ route('products.deleteImage', '') }}/${imageId}`, {
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
                alert(data.message || '{{ __("messages.Error_Deleting_Image") }}');
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