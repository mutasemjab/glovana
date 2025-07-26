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
                
                <!-- Search and Filter Section -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('products.index') }}" class="row g-3">
                        <!-- Search Input -->
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.Search') }}</label>
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="{{ __('messages.Search_Products') }}"
                                   value="{{ request('search') }}">
                        </div>

                        <!-- Category Filter -->
                        <div class="col-md-2">
                            <label class="form-label">{{ __('messages.Category') }}</label>
                            <select name="category_id" class="form-control">
                                <option value="">{{ __('messages.All_Categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? $category->name_ar : $category->name_en }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="col-md-2">
                            <label class="form-label">{{ __('messages.Min_Price') }}</label>
                            <input type="number" 
                                   name="min_price" 
                                   class="form-control" 
                                   placeholder="0"
                                   value="{{ request('min_price') }}"
                                   min="0" 
                                   step="0.01">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('messages.Max_Price') }}</label>
                            <input type="number" 
                                   name="max_price" 
                                   class="form-control" 
                                   placeholder="1000"
                                   value="{{ request('max_price') }}"
                                   min="0" 
                                   step="0.01">
                        </div>

                        <!-- Quantity Status Filter -->
                        <div class="col-md-2">
                            <label class="form-label">{{ __('messages.Stock_Status') }}</label>
                            <select name="quantity_status" class="form-control">
                                <option value="">{{ __('messages.All_Stock') }}</option>
                                <option value="out_of_stock" {{ request('quantity_status') == 'out_of_stock' ? 'selected' : '' }}>
                                    {{ __('messages.out_of_stock') }}
                                </option>
                                <option value="low_stock" {{ request('quantity_status') == 'low_stock' ? 'selected' : '' }}>
                                    {{ __('messages.low_stock') }} (≤{{ $minimumQuantity }})
                                </option>
                                <option value="in_stock" {{ request('quantity_status') == 'in_stock' ? 'selected' : '' }}>
                                    {{ __('messages.in_stock') }} (>{{ $minimumQuantity }})
                                </option>
                            </select>
                        </div>

                      

                        <div class="col-md-2">
                            <label class="form-label">{{ __('messages.Discount') }}</label>
                            <select name="has_discount" class="form-control">
                                <option value="">{{ __('messages.All') }}</option>
                                <option value="yes" {{ request('has_discount') == 'yes' ? 'selected' : '' }}>
                                    {{ __('messages.With_Discount') }}
                                </option>
                                <option value="no" {{ request('has_discount') == 'no' ? 'selected' : '' }}>
                                    {{ __('messages.Without_Discount') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('messages.Sort_By') }}</label>
                            <select name="sort_by" class="form-control">
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>
                                    {{ __('messages.Date_Added') }}
                                </option>
                                <option value="name_en" {{ request('sort_by') == 'name_en' ? 'selected' : '' }}>
                                    {{ __('messages.Name') }}
                                </option>
                                <option value="price" {{ request('sort_by') == 'price' ? 'selected' : '' }}>
                                    {{ __('messages.Price') }}
                                </option>
                                <option value="total_quantity" {{ request('sort_by') == 'total_quantity' ? 'selected' : '' }}>
                                    {{ __('messages.Quantity') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ __('messages.Order') }}</label>
                            <select name="sort_order" class="form-control">
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>
                                    {{ __('messages.Descending') }}
                                </option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>
                                    {{ __('messages.Ascending') }}
                                </option>
                            </select>
                        </div>

                        <!-- Filter Buttons -->
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> {{ __('messages.Filter') }}
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> {{ __('messages.Clear') }}
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <!-- Results Info -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <small class="text-muted">
                                {{ __('messages.Showing') }} {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} 
                                {{ __('messages.of') }} {{ $products->total() }} {{ __('messages.results') }}
                            </small>
                        </div>
                        @if(request()->hasAny(['search', 'category_id', 'min_price', 'max_price', 'quantity_status', 'sold_status', 'has_discount']))
                            <div>
                                <span class="badge bg-info">{{ __('messages.Filters_Applied') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.Image') }}</th>
                                    <th>{{ __('messages.Name') }}</th>
                                    <th>{{ __('messages.Category') }}</th>
                                    <th>{{ __('messages.Price') }}</th>
                                    <th>{{ __('messages.Price_After_Discount') }}</th>
                                    <th>{{ __('messages.Quantity') }}</th>
                                    <th>{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    <tr class="{{ $product->total_quantity <= $minimumQuantity ? 'table-warning' : '' }}">
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
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">{{ $product->total_quantity }}</span>
                                                @if($product->total_quantity == 0)
                                                    <span class="badge bg-danger">{{ __('messages.out_of_stock') }}</span>
                                                @elseif($product->total_quantity <= $minimumQuantity)
                                                    <span class="badge bg-warning">{{ __('messages.low_stock') }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ __('messages.in_stock') }}</span>
                                                @endif
                                            </div>
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
                                        <td colspan="8" class="text-center">
                                            {{ __('messages.No_Products_Found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table-warning {
    background-color: #fff3cd !important;
}
.low-stock {
    background-color: #fff3cd;
}
</style>
@endsection