@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.Edit_Delivery') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('deliveries.update', $delivery->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="place" class="form-label">
                                {{ __('messages.Place') }}
                            </label>
                            <input type="text" 
                                   class="form-control @error('place') is-invalid @enderror" 
                                   id="place" 
                                   name="place" 
                                   value="{{ old('place', $delivery->place) }}" 
                                   placeholder="{{ __('messages.Enter_Place') }}"
                                   required>
                            @error('place')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">
                                {{ __('messages.Price') }}
                            </label>
                            <input type="number" 
                                   class="form-control @error('price') is-invalid @enderror" 
                                   id="price" 
                                   name="price" 
                                   value="{{ old('price', $delivery->price) }}" 
                                   step="0.01"
                                   min="0"
                                   placeholder="{{ __('messages.Enter_Price') }}"
                                   required>
                            @error('price')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                {{ __('messages.Update') }}
                            </button>
                            <a href="{{ route('deliveries.index') }}" class="btn btn-secondary">
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