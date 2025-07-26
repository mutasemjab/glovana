@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.coupons_and_users') }}</h4>
                </div>
                <div class="card-body">
                    @if($coupons->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>{{ __('messages.id') }}</th>
                                        <th>{{ __('messages.code') }}</th>
                                        <th>{{ __('messages.amount') }}</th>
                                        <th>{{ __('messages.minimum_total') }}</th>
                                        <th>{{ __('messages.type') }}</th>
                                        <th>{{ __('messages.expired_at') }}</th>
                                        <th>{{ __('messages.users_count') }}</th>
                                        <th>{{ __('messages.users') }}</th>
                                        <th>{{ __('messages.created_at') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($coupons as $coupon)
                                        <tr>
                                            <td>{{ $coupon->id }}</td>
                                            <td>
                                                <span class="badge badge-primary">{{ $coupon->code }}</span>
                                            </td>
                                            <td>JD {{ number_format($coupon->amount, 2) }}</td>
                                            <td>JD {{ number_format($coupon->minimum_total, 2) }}</td>
                                            <td>
                                                @if($coupon->type == 1)
                                                    <span class="badge badge-success">{{ __('messages.products') }}</span>
                                                @else
                                                    <span class="badge badge-info">{{ __('messages.provider') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="@if(\Carbon\Carbon::parse($coupon->expired_at)->isPast()) text-danger @else text-success @endif">
                                                    {{ \Carbon\Carbon::parse($coupon->expired_at)->format('M d, Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">{{ $coupon->users->count() }}</span>
                                            </td>
                                            <td>
                                                @if($coupon->users->count() > 0)
                                                    <div class="users-list">
                                                        @foreach($coupon->users as $user)
                                                            <div class="user-item mb-1">
                                                                <small class="badge badge-light">
                                                                    {{ $user->name }} ({{ $user->email }})
                                                                </small>
                                                                <br>
                                                                <small class="text-muted">
                                                                    {{ __('messages.used') }}: {{ \Carbon\Carbon::parse($user->pivot->created_at)->format('M d, Y H:i') }}
                                                                </small>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">{{ __('messages.no_users_yet') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $coupon->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $coupons->links() }}
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <h5>{{ __('messages.no_coupons_found') }}</h5>
                            <p>{{ __('messages.no_coupons_in_system') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.users-list {
    max-height: 200px;
    overflow-y: auto;
}
.user-item {
    padding: 2px 0;
    border-bottom: 1px solid #f0f0f0;
}
.user-item:last-child {
    border-bottom: none;
}
</style>
@endsection