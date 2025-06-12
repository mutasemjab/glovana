@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4>{{ __('messages.Products') }}</h4>
                    <a href="{{ route('products.create') }}" class="btn btn-primary">
                        {{ __('messages.Add_Product') }}
                    </a>
                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.Image') }}</th>
                                    <th>{{ __('messages.Name') }}</th>
                                    <th>{{ __('messages.Category') }}</th>
                                    <th>{{ __('messages.Price') }}</th>
                                    <th>{{ __('messages.Price_After_Discount') }}</th>
                                    <th>{{ __('messages.Sold') }}</th>
                                    <th>{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    <tr>
                                        <td>
                                            @if($product->first_image)
                                                <img src="{{ asset('assets/admin/uploads/' . $product->first_image) }}" 
                                                     alt="{{ $product->name_en }}" 
                                                     class="img-thumbnail" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <small class="text-muted">{{ __('messages.No_Image') }}</small>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ app()->getLocale() == 'ar' ? $product->name_ar : $product->name_en }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ Str::limit(app()->getLocale() == 'ar' ? $product->description_ar : $product->description_en, 50) }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($product->category_name_en)
                                                <span class="badge bg-info">
                                                    {{ app()->getLocale() == 'ar' ? $product->category_name_ar : $product->category_name_en }}
                                                </span>
                                            @else
                                                <span class="text-muted">{{ __('messages.No_Category') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($product->price, 2) }} {{ __('messages.Currency') }}</td>
                                        <td>
                                            @if($product->price_after_discount && $product->price_after_discount != $product->price)
                                                <span class="text-success fw-bold">
                                                    {{ number_format($product->price_after_discount, 2) }} {{ __('messages.Currency') }}
                                                </span>
                                                @if($product->discount_percentage)
                                                    <br><small class="text-danger">-{{ $product->discount_percentage }}%</small>
                                                @endif
                                            @else
                                                <span class="text-muted">{{ __('messages.No_Discount') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $product->sold ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $product->sold ?: __('messages.Available') }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('products.edit', $product->id) }}" 
                                               class="btn btn-sm btn-warning">
                                                {{ __('messages.Edit') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            {{ __('messages.No_Products_Found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection