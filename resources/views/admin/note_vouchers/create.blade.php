@extends('layouts.admin')

@section('title', __('messages.create_note_voucher'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('messages.create_note_voucher') }}</h3>
                    <a href="{{ route('note-vouchers.index') }}" class="btn btn-secondary float-end">
                        <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('note-vouchers.store') }}" method="POST" id="voucherForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="number" class="form-label">{{ __('messages.number') }} *</label>
                                    <input type="number" 
                                           class="form-control @error('number') is-invalid @enderror" 
                                           id="number" 
                                           name="number" 
                                           value="{{ old('number', $nextNumber) }}" 
                                           required>
                                    @error('number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">{{ __('messages.type') }} *</label>
                                    <select class="form-control @error('type') is-invalid @enderror" 
                                            id="type" 
                                            name="type" 
                                            required>
                                        <option value="">{{ __('messages.select_type') }}</option>
                                        <option value="1" {{ old('type') == '1' ? 'selected' : '' }}>
                                            {{ __('messages.in') }}
                                        </option>
                                        <option value="2" {{ old('type') == '2' ? 'selected' : '' }}>
                                            {{ __('messages.out') }}
                                        </option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_note_voucher" class="form-label">{{ __('messages.date') }} *</label>
                                    <input type="date" 
                                           class="form-control @error('date_note_voucher') is-invalid @enderror" 
                                           id="date_note_voucher" 
                                           name="date_note_voucher" 
                                           value="{{ old('date_note_voucher', date('Y-m-d')) }}" 
                                           required>
                                    @error('date_note_voucher')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="warehouse_id" class="form-label">{{ __('messages.warehouse') }}</label>
                                    <select class="form-control @error('warehouse_id') is-invalid @enderror" 
                                            id="warehouse_id" 
                                            name="warehouse_id">
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" 
                                                    {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('warehouse_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order_id" class="form-label">{{ __('messages.order') }}</label>
                                    <select class="form-control @error('order_id') is-invalid @enderror" 
                                            id="order_id" 
                                            name="order_id">
                                        <option value="">{{ __('messages.select_order') }}</option>
                                        @foreach ($orders as $order)
                                            <option value="{{ $order->id }}" 
                                                    {{ old('order_id') == $order->id ? 'selected' : '' }}>
                                                {{ $order->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('order_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="note" class="form-label">{{ __('messages.note') }}</label>
                                    <textarea class="form-control @error('note') is-invalid @enderror" 
                                              id="note" 
                                              name="note" 
                                              rows="3">{{ old('note') }}</textarea>
                                    @error('note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>{{ __('messages.products') }}</h5>
                            <button type="button" class="btn btn-success btn-sm" id="addProduct">
                                <i class="fas fa-plus"></i> {{ __('messages.add_product') }}
                            </button>
                        </div>

                        <div id="products-container">
                            <!-- Products will be added here -->
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('messages.save') }}
                            </button>
                            <a href="{{ route('note-vouchers.index') }}" class="btn btn-secondary">
                                {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let productIndex = 0;
    const productsContainer = document.getElementById('products-container');
    const addProductBtn = document.getElementById('addProduct');

    const products = @json($products);

    function createProductRow(index) {
        return `
            <div class="row product-row mb-3" data-index="${index}">
                <div class="col-md-4">
                    <select class="form-control" name="products[${index}][product_id]" required>
                        <option value="">{{ __('messages.select_product') }}</option>
                        ${products.map(product => 
                            `<option value="${product.id}">${product.name}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" 
                           class="form-control" 
                           name="products[${index}][quantity]" 
                           placeholder="{{ __('messages.quantity') }}" 
                           min="1" 
                           required>
                </div>
                <div class="col-md-4">
                    <input type="text" 
                           class="form-control" 
                           name="products[${index}][note]" 
                           placeholder="{{ __('messages.note') }}">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-product">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }

    addProductBtn.addEventListener('click', function() {
        const productRow = createProductRow(productIndex);
        productsContainer.insertAdjacentHTML('beforeend', productRow);
        productIndex++;
    });

    productsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-product') || e.target.parentElement.classList.contains('remove-product')) {
            const row = e.target.closest('.product-row');
            row.remove();
        }
    });

    // Add initial product row
    addProductBtn.click();
});
</script>
@endsection