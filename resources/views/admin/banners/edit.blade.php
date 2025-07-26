@extends('layouts.admin')
@section('title')
    {{ __('messages.Edit') }} {{ __('messages.banners') }}
@endsection



@section('contentheaderlink')
    <a href="{{ route('banners.index') }}"> {{ __('messages.banners') }} </a>
@endsection

@section('contentheaderactive')
    {{ __('messages.Edit') }}
@endsection


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center"> {{ __('messages.Edit') }} {{ __('messages.banners') }} </h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">


            <form action="{{ route('banners.update', $data['id']) }}" method="post" enctype='multipart/form-data'>
                <div class="row">
                    @csrf
                    @method('PUT')


                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-control" onchange="toggleFields()">
                                <option value="1" {{ old('type', $data->type ?? '') == 1 ? 'selected' : '' }}>Store</option>
                                <option value="2" {{ old('type', $data->type ?? '') == 2 ? 'selected' : '' }}>Provider Type</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12" id="product-field">
                        <div class="form-group">
                            <label for="product_id">Product</label>
                            <select name="product_id" class="form-control">
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" {{ (old('product_id') ?? $data->product_id ?? '') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12" id="provider-type-field">
                        <div class="form-group">
                            <label for="provider_type_id">Provider Type</label>
                            <select name="provider_type_id" class="form-control">
                                @foreach ($providerTypes as $providerType)
                                    <option value="{{ $providerType->id }}" {{ (old('provider_type_id') ?? $data->provider_type_id ?? '') == $providerType->id ? 'selected' : '' }}>
                                        {{ $providerType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="form-group">
                            <img src="" id="image-preview" alt="Selected Image" height="50px" width="50px"
                                style="display: none;">
                            <button class="btn"> photo</button>
                            <input type="file" id="Item_img" name="photo" class="form-control"
                                onchange="previewImage()">
                            @error('photo')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>





                    <div class="col-md-12">
                        <div class="form-group text-center">
                            <button id="do_add_item_cardd" type="submit" class="btn btn-primary btn-sm">
                                {{ __('messages.Update') }}</button>
                            <a href="{{ route('banners.index') }}"
                                class="btn btn-sm btn-danger">{{ __('messages.Cancel') }}</a>

                        </div>
                    </div>

                </div>
            </form>



        </div>




    </div>
    </div>
@endsection

@section('script')
    <script>
        function toggleFields() {
            var type = document.getElementById('type').value;
            document.getElementById('product-field').style.display = type == 1 ? 'block' : 'none';
            document.getElementById('provider-type-field').style.display = type == 2 ? 'block' : 'none';
        }

        document.addEventListener("DOMContentLoaded", function () {
            toggleFields(); // Set fields based on default value
        });

    </script>
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
