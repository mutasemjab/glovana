@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4>{{ __('messages.Deliveries') }}</h4>
                    <a href="{{ route('deliveries.create') }}" class="btn btn-primary">
                        {{ __('messages.Add_Delivery') }}
                    </a>
                </div>
                <div class="card-body">
                  
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.ID') }}</th>
                                    <th>{{ __('messages.Place') }}</th>
                                    <th>{{ __('messages.Price') }}</th>
                                    <th>{{ __('messages.Created_At') }}</th>
                                    <th>{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deliveries as $delivery)
                                    <tr>
                                        <td>{{ $delivery->id }}</td>
                                        <td>{{ $delivery->place }}</td>
                                        <td>{{ number_format($delivery->price, 2) }} {{ __('messages.Currency') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($delivery->created_at)->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('deliveries.edit', $delivery->id) }}" 
                                               class="btn btn-sm btn-warning">
                                                {{ __('messages.Edit') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            {{ __('messages.No_Deliveries_Found') }}
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