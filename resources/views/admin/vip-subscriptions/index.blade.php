@extends('layouts.admin')

@section('title')
{{ __('messages.vip_subscriptions') }}
@endsection

@section('css')
<style>
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
    border-left: 4px solid #007bff;
}

.stat-card.active { border-left-color: #28a745; }
.stat-card.expired { border-left-color: #dc3545; }
.stat-card.expiring { border-left-color: #ffc107; }
.stat-card.revenue { border-left-color: #6f42c1; }

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #333;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
    margin-top: 5px;
}

.filters {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table-responsive {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active { background: #d4edda; color: #155724; }
.status-inactive { background: #f8d7da; color: #721c24; }
.status-expired { background: #f5c6cb; color: #491a22; }

.payment-paid { background: #d1ecf1; color: #0c5460; }
.payment-unpaid { background: #fff3cd; color: #856404; }

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
@endsection



@section('content')
<!-- Statistics Cards -->
<div class="stats-cards">
    <div class="stat-card">
        <div class="stat-number">{{ $stats['total'] }}</div>
        <div class="stat-label">{{ __('messages.total_subscriptions') }}</div>
    </div>
    <div class="stat-card active">
        <div class="stat-number">{{ $stats['active'] }}</div>
        <div class="stat-label">{{ __('messages.active_subscriptions') }}</div>
    </div>
    <div class="stat-card expired">
        <div class="stat-number">{{ $stats['expired'] }}</div>
        <div class="stat-label">{{ __('messages.expired_subscriptions') }}</div>
    </div>
    <div class="stat-card expiring">
        <div class="stat-number">{{ $stats['expiring_soon'] }}</div>
        <div class="stat-label">{{ __('messages.expiring_soon') }}</div>
    </div>
    <div class="stat-card revenue">
        <div class="stat-number">{{ number_format($stats['total_revenue'], 2) }}</div>
        <div class="stat-label">{{ __('messages.total_revenue') }} ({{ __('messages.currency') }})</div>
    </div>
</div>

<!-- Filters -->
<div class="filters">
    <form method="GET" action="{{ route('admin.vip-subscriptions.index') }}">
        <div class="row">
            <div class="col-md-3">
                <label>{{ __('messages.search') }}</label>
                <input type="text" name="search" class="form-control" 
                       value="{{ request('search') }}" 
                       placeholder="{{ __('messages.search_provider_salon') }}">
            </div>
            <div class="col-md-2">
                <label>{{ __('messages.status') }}</label>
                <select name="status" class="form-control">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                    <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>{{ __('messages.expired') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>{{ __('messages.payment_status') }}</label>
                <select name="payment_status" class="form-control">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="1" {{ request('payment_status') == '1' ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
                    <option value="2" {{ request('payment_status') == '2' ? 'selected' : '' }}>{{ __('messages.unpaid') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>{{ __('messages.date_from') }}</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label>{{ __('messages.date_to') }}</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block">{{ __('messages.filter') }}</button>
            </div>
        </div>
    </form>
</div>

<!-- Actions -->
<div class="row mb-3">
    <div class="col-md-6">
        <a href="{{ route('admin.vip-subscriptions.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> {{ __('messages.add_subscription') }}
        </a>
    </div>
    <div class="col-md-6 text-right">
        <button type="button" class="btn btn-warning" onclick="updateExpiredSubscriptions()">
            <i class="fas fa-sync"></i> {{ __('messages.update_expired') }}
        </button>
    </div>
</div>

<!-- Table -->
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="thead-light">
            <tr>
                <th>{{ __('messages.id') }}</th>
                <th>{{ __('messages.provider') }}</th>
                <th>{{ __('messages.salon_name') }}</th>
                <th>{{ __('messages.start_date') }}</th>
                <th>{{ __('messages.end_date') }}</th>
                <th>{{ __('messages.amount_paid') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.payment_status') }}</th>
                <th>{{ __('messages.days_remaining') }}</th>
                <th>{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subscriptions as $subscription)
            <tr>
                <td>{{ $subscription->id }}</td>
                <td>{{ $subscription->providerType->provider->name_of_manager }}</td>
                <td>{{ $subscription->providerType->name }}</td>
                <td>{{ $subscription->start_date->format('Y-m-d') }}</td>
                <td>{{ $subscription->end_date->format('Y-m-d') }}</td>
                <td>{{ number_format($subscription->amount_paid, 2) }} {{ __('messages.currency') }}</td>
                <td>
                    <span class="status-badge status-{{ $subscription->status == 1 ? 'active' : ($subscription->status == 3 ? 'expired' : 'inactive') }}">
                        {{ $subscription->status_text }}
                    </span>
                </td>
                <td>
                    <span class="status-badge payment-{{ $subscription->payment_status == 1 ? 'paid' : 'unpaid' }}">
                        {{ $subscription->payment_status_text }}
                    </span>
                </td>
                <td>
                    @if($subscription->is_expired)
                        <span class="text-danger">{{ __('messages.expired') }}</span>
                    @elseif($subscription->days_remaining <= 7)
                        <span class="text-warning">{{ $subscription->days_remaining }} {{ __('messages.days') }}</span>
                    @else
                        <span class="text-success">{{ $subscription->days_remaining }} {{ __('messages.days') }}</span>
                    @endif
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('admin.vip-subscriptions.show', $subscription) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.vip-subscriptions.edit', $subscription) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.vip-subscriptions.destroy', $subscription) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('messages.confirm_delete') }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center py-5">
                    {{ __('messages.no_subscriptions_found') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-3">
    {{ $subscriptions->appends(request()->query())->links() }}
</div>
@endsection

@section('js')
<script>
function updateExpiredSubscriptions() {
    if (confirm('{{ __('messages.confirm_update_expired') }}')) {
        fetch('{{ route('admin.vip-subscriptions.update-expired') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message + ' (' + data.count + ')');
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __('messages.error_occurred') }}');
        });
    }
}
</script>
@endsection