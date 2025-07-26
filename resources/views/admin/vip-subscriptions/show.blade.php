@extends('layouts.admin')

@section('title')
{{ __('messages.view_vip_subscription') }}
@endsection

@section('css')
<style>
.info-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 20px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #555;
    flex: 0 0 200px;
}

.info-value {
    color: #333;
    flex: 1;
}

.status-badge {
    padding: 6px 15px;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active { background: #d4edda; color: #155724; }
.status-inactive { background: #f8d7da; color: #721c24; }
.status-expired { background: #f5c6cb; color: #491a22; }

.payment-paid { background: #d1ecf1; color: #0c5460; }
.payment-unpaid { background: #fff3cd; color: #856404; }

.alert-warning { 
    border-left: 4px solid #ffc107; 
}

.alert-danger { 
    border-left: 4px solid #dc3545; 
}

.alert-success { 
    border-left: 4px solid #28a745; 
}

.timeline-badge {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-right: 15px;
}

.timeline-start { background: #28a745; }
.timeline-current { background: #ffc107; }
.timeline-end { background: #dc3545; }
</style>
@endsection



@section('content')
<!-- Subscription Status Alert -->
@if($vipSubscription->is_expired)
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        {{ __('messages.subscription_expired') }}
    </div>
@elseif($vipSubscription->days_remaining <= 7 && $vipSubscription->status == 1)
    <div class="alert alert-warning">
        <i class="fas fa-clock"></i>
        {{ __('messages.subscription_expiring_soon') }}: {{ $vipSubscription->days_remaining }} {{ __('messages.days_remaining') }}
    </div>
@elseif($vipSubscription->is_active)
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ __('messages.subscription_active') }}
    </div>
@endif

<div class="row">
    <!-- Subscription Details -->
    <div class="col-md-8">
        <div class="info-card">
            <h5 class="mb-4">{{ __('messages.subscription_details') }}</h5>
            
            <div class="info-row">
                <span class="info-label">{{ __('messages.subscription_id') }}:</span>
                <span class="info-value">#{{ $vipSubscription->id }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ __('messages.provider_name') }}:</span>
                <span class="info-value">{{ $vipSubscription->providerType->provider->name_of_manager }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ __('messages.salon_name') }}:</span>
                <span class="info-value">{{ $vipSubscription->providerType->name }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ __('messages.service_type') }}:</span>
                <span class="info-value">{{ $vipSubscription->providerType->type->name_ar }} / {{ $vipSubscription->providerType->type->name_en }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ __('messages.booking_type') }}:</span>
                <span class="info-value">
                    @if($vipSubscription->providerType->type->booking_type == 'hourly')
                        {{ __('messages.hourly_provider') }}
                    @else
                        {{ __('messages.service_salon') }}
                    @endif
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ __('messages.start_date') }}:</span>
                <span class="info-value">{{ $vipSubscription->start_date->format('Y-m-d') }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ __('messages.end_date') }}:</span>
                <span class="info-value">{{ $vipSubscription->end_date->format('Y-m-d') }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ __('messages.duration') }}:</span>
                <span class="info-value">{{ $vipSubscription->start_date->diffInDays($vipSubscription->end_date) }} {{ __('messages.days') }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ __('messages.amount_paid') }}:</span>
                <span class="info-value">{{ number_format($vipSubscription->amount_paid, 2) }} {{ __('messages.currency') }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ __('messages.payment_method') }}:</span>
                <span class="info-value">
                    @if($vipSubscription->payment_method)
                        {{ __('messages.' . $vipSubscription->payment_method) }}
                    @else
                        {{ __('messages.not_specified') }}
                    @endif
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ __('messages.subscription_status') }}:</span>
                <span class="info-value">
                    <span class="status-badge status-{{ $vipSubscription->status == 1 ? 'active' : ($vipSubscription->status == 3 ? 'expired' : 'inactive') }}">
                        {{ $vipSubscription->status_text }}
                    </span>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ __('messages.payment_status') }}:</span>
                <span class="info-value">
                    <span class="status-badge payment-{{ $vipSubscription->payment_status == 1 ? 'paid' : 'unpaid' }}">
                        {{ $vipSubscription->payment_status_text }}
                    </span>
                </span>
            </div>

            @if($vipSubscription->notes)
            <div class="info-row">
                <span class="info-label">{{ __('messages.notes') }}:</span>
                <span class="info-value">{{ $vipSubscription->notes }}</span>
            </div>
            @endif

            <div class="info-row">
                <span class="info-label">{{ __('messages.created_at') }}:</span>
                <span class="info-value">{{ $vipSubscription->created_at->format('Y-m-d H:i:s') }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ __('messages.updated_at') }}:</span>
                <span class="info-value">{{ $vipSubscription->updated_at->format('Y-m-d H:i:s') }}</span>
            </div>
        </div>
    </div>

    <!-- Timeline & Actions -->
    <div class="col-md-4">
        <!-- Timeline -->
        <div class="info-card">
            <h5 class="mb-4">{{ __('messages.subscription_timeline') }}</h5>
            
            <div class="d-flex align-items-center mb-3">
                <div class="timeline-badge timeline-start">
                    <i class="fas fa-play"></i>
                </div>
                <div>
                    <strong>{{ __('messages.start_date') }}</strong><br>
                    <small>{{ $vipSubscription->start_date->format('Y-m-d') }}</small>
                </div>
            </div>

            <div class="d-flex align-items-center mb-3">
                <div class="timeline-badge timeline-current">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <strong>{{ __('messages.today') }}</strong><br>
                    <small>{{ date('Y-m-d') }}</small>
                    @if(!$vipSubscription->is_expired)
                        <br><span class="text-success">{{ $vipSubscription->days_remaining }} {{ __('messages.days_remaining') }}</span>
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center">
                <div class="timeline-badge timeline-end">
                    <i class="fas fa-stop"></i>
                </div>
                <div>
                    <strong>{{ __('messages.end_date') }}</strong><br>
                    <small>{{ $vipSubscription->end_date->format('Y-m-d') }}</small>
                    @if($vipSubscription->is_expired)
                        <br><span class="text-danger">{{ __('messages.expired') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="info-card">
            <h5 class="mb-3">{{ __('messages.actions') }}</h5>
            
            <div class="d-grid gap-2">
                <a href="{{ route('admin.vip-subscriptions.edit', $vipSubscription) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> {{ __('messages.edit') }}
                </a>
                
                @if($vipSubscription->status != 3 && !$vipSubscription->is_expired)
                <form action="{{ route('admin.vip-subscriptions.update', $vipSubscription) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="3">
                    <input type="hidden" name="provider_type_id" value="{{ $vipSubscription->provider_type_id }}">
                    <input type="hidden" name="start_date" value="{{ $vipSubscription->start_date->format('Y-m-d') }}">
                    <input type="hidden" name="end_date" value="{{ $vipSubscription->end_date->format('Y-m-d') }}">
                    <input type="hidden" name="amount_paid" value="{{ $vipSubscription->amount_paid }}">
                    <input type="hidden" name="payment_status" value="{{ $vipSubscription->payment_status }}">
                    <input type="hidden" name="payment_method" value="{{ $vipSubscription->payment_method }}">
                    <input type="hidden" name="notes" value="{{ $vipSubscription->notes }}">
                    <button type="submit" class="btn btn-warning w-100" onclick="return confirm('{{ __('messages.confirm_expire_subscription') }}')">
                        <i class="fas fa-ban"></i> {{ __('messages.expire_subscription') }}
                    </button>
                </form>
                @endif
                
                <form action="{{ route('admin.vip-subscriptions.destroy', $vipSubscription) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('{{ __('messages.confirm_delete') }}')">
                        <i class="fas fa-trash"></i> {{ __('messages.delete') }}
                    </button>
                </form>
                
                <a href="{{ route('admin.vip-subscriptions.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('messages.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection