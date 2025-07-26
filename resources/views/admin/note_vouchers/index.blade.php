@extends('layouts.admin')

@section('title', __('messages.note_vouchers'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('messages.note_vouchers') }}</h3>
                    <a href="{{ route('note-vouchers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('messages.add_new') }}
                    </a>
                </div>
                <div class="card-body">
                   

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.number') }}</th>
                                    <th>{{ __('messages.type') }}</th>
                                    <th>{{ __('messages.date') }}</th>
                                    <th>{{ __('messages.warehouse') }}</th>
                                    <th>{{ __('messages.order') }}</th>
                                    <th>{{ __('messages.products_count') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($noteVouchers as $voucher)
                                    <tr>
                                        <td>{{ $voucher->number }}</td>
                                        <td>
                                            <span class="badge bg-{{ $voucher->type_class }}">
                                                {{ $voucher->type_text }}
                                            </span>
                                        </td>
                                        <td>{{ $voucher->date_note_voucher->format('Y-m-d') }}</td>
                                        <td>{{ $voucher->warehouse->name ?? '-' }}</td>
                                        <td>{{ $voucher->order->id ?? '-' }}</td>
                                        <td>{{ $voucher->voucher_products_count }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('note-vouchers.show', $voucher) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('note-vouchers.edit', $voucher) }}" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('note-vouchers.destroy', $voucher) }}" 
                                                      method="POST" 
                                                      style="display: inline-block;"
                                                      onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('messages.no_data_available') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $noteVouchers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection