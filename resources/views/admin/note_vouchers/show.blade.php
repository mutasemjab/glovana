@extends('layouts.admin')

@section('title', __('messages.view_note_voucher'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('messages.view_note_voucher') }} #{{ $noteVoucher->number }}</h3>
                    <div>
                        <a href="{{ route('note-vouchers.edit', $noteVoucher) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> {{ __('messages.edit') }}
                        </a>
                        <a href="{{ route('note-vouchers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <h5>{{ __('messages.voucher_information') }}</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>{{ __('messages.number') }}:</strong></td>
                                        <td>{{ $noteVoucher->number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('messages.type') }}:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $noteVoucher->type_class }} fs-6">
                                                {{ $noteVoucher->type_text }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('messages.date') }}:</strong></td>
                                        <td>{{ $noteVoucher->date_note_voucher->format('Y-m-d') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('messages.warehouse') }}:</strong></td>
                                        <td>{{ $noteVoucher->warehouse->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('messages.order') }}:</strong></td>
                                        <td>{{ $noteVoucher->order->id ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('messages.created_at') }}:</strong></td>
                                        <td>{{ $noteVoucher->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('messages.updated_at') }}:</strong></td>
                                        <td>{{ $noteVoucher->updated_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            @if($noteVoucher->note)
                                <div class="info-box">
                                    <h5>{{ __('messages.note') }}</h5>
                                    <div class="alert alert-info">
                                        {{ $noteVoucher->note }}
                                    </div>
                                </div>
                            @endif
                            
                            <div class="info-box">
                                <h5>{{ __('messages.statistics') }}</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h4>{{ $noteVoucher->voucherProducts->count() }}</h4>
                                                <p class="mb-0">{{ __('messages.total_products') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h4>{{ $noteVoucher->voucherProducts->sum('quantity') }}</h4>
                                                <p class="mb-0">{{ __('messages.total_quantity') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <h5>{{ __('messages.products') }}</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('messages.product') }}</th>
                                            <th>{{ __('messages.quantity') }}</th>
                                            <th>{{ __('messages.note') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($noteVoucher->voucherProducts as $index => $voucherProduct)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($voucherProduct->product)
                                                            <div>
                                                                <strong>{{ $voucherProduct->product->name }}</strong>
                                                                @if($voucherProduct->product->code)
                                                                    <br>
                                                                    <small class="text-muted">{{ __('messages.code') }}: {{ $voucherProduct->product->code }}</small>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-danger">{{ __('messages.product_not_found') }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info fs-6">{{ $voucherProduct->quantity }}</span>
                                                </td>
                                                <td>{{ $voucherProduct->note ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">{{ __('messages.no_products_found') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <form action="{{ route('note-vouchers.destroy', $noteVoucher) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> {{ __('messages.delete') }}
                                    </button>
                                </form>
                                
                                <div>
                                    <a href="{{ route('note-vouchers.edit', $noteVoucher) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> {{ __('messages.edit') }}
                                    </a>
                                    <button onclick="window.print()" class="btn btn-info">
                                        <i class="fas fa-print"></i> {{ __('messages.print') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header .btn, .d-flex.justify-content-between {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .info-box {
        page-break-inside: avoid;
    }
}

.info-box {
    margin-bottom: 1.5rem;
}

.info-box h5 {
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}
</style>
@endsection