@extends('layouts.admin')

@section('title')
{{ __('messages.fines_discounts_management') }}
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

.stat-card.fines { border-left-color: #dc3545; }
.stat-card.discounts { border-left-color: #28a745; }
.stat-card.pending { border-left-color: #ffc107; }
.stat-card.revenue { border-left-color: #6f42c1; }

.stat-number {
    font-size: 1.8rem;
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

.type-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.type-fine { background: #f8d7da; color: #721c24; }
.type-discount { background: #d4edda; color: #155724; }

.category-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.category-automatic { background: #d1ecf1; color: #0c5460; }
.category-manual { background: #e2e3e5; color: #383d41; }

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-applied { background: #d4edda; color: #155724; }
.status-reversed { background: #f8d7da; color: #721c24; }
.status-failed { background: #f5c6cb; color: #491a22; }

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.bulk-actions {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    display: none;
}

.bulk-actions.show {
    display: block;
}
</style>
@endsection



@section('content')
<!-- Statistics Cards -->
<div class="stats-cards">
    <div class="stat-card fines">
        <div class="stat-number">{{ $stats['total_fines'] }}</div>
        <div class="stat-label">{{ __('messages.total_fines') }}</div>
    </div>

    <div class="stat-card pending">
        <div class="stat-number">{{ number_format($stats['pending_amount'], 2) }}</div>
        <div class="stat-label">{{ __('messages.pending_amount') }} ({{ __('messages.currency') }})</div>
    </div>
    <div class="stat-card revenue">
        <div class="stat-number">{{ number_format($stats['total_revenue_impact'], 2) }}</div>
        <div class="stat-label">{{ __('messages.revenue_impact') }} ({{ __('messages.currency') }})</div>
    </div>
</div>

<!-- Filters -->
<div class="filters">
    <form method="GET" action="{{ route('fines-discounts.index') }}">
        <div class="row">
            <div class="col-md-2">
                <label>{{ __('messages.search') }}</label>
                <input type="text" name="search" class="form-control" 
                       value="{{ request('search') }}" 
                       placeholder="{{ __('messages.search_reason_notes') }}">
            </div>
         
            <div class="col-md-2">
                <label>{{ __('messages.category') }}</label>
                <select name="category" class="form-control">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="1" {{ request('category') == '1' ? 'selected' : '' }}>{{ __('messages.automatic') }}</option>
                    <option value="2" {{ request('category') == '2' ? 'selected' : '' }}>{{ __('messages.manual') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>{{ __('messages.status') }}</label>
                <select name="status" class="form-control">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>{{ __('messages.applied') }}</option>
                    <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>{{ __('messages.reversed') }}</option>
                    <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>{{ __('messages.failed') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>{{ __('messages.entity_type') }}</label>
                <select name="entity_type" class="form-control">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="user" {{ request('entity_type') == 'user' ? 'selected' : '' }}>{{ __('messages.users') }}</option>
                    <option value="provider" {{ request('entity_type') == 'provider' ? 'selected' : '' }}>{{ __('messages.providers') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block">{{ __('messages.filter') }}</button>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3">
                <label>{{ __('messages.date_from') }}</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label>{{ __('messages.date_to') }}</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
        </div>
    </form>
</div>

<!-- Actions -->
<div class="row mb-3">
    <div class="col-md-6">
        <div class="btn-group">
            <a href="{{ route('fines-discounts.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> {{ __('messages.add_manual') }}
            </a>
            <a href="{{ route('fines-discounts.settings') }}" class="btn btn-secondary">
                <i class="fas fa-cog"></i> {{ __('messages.settings') }}
            </a>
        </div>
    </div>
  
</div>

<!-- Bulk Actions -->
<div class="bulk-actions" id="bulk-actions">
    <div class="row">
        <div class="col-md-8">
            <span class="selected-count">0</span> {{ __('messages.items_selected') }}
        </div>
        <div class="col-md-4 text-right">
            <button type="button" class="btn btn-success btn-sm" onclick="bulkApply()">
                <i class="fas fa-check"></i> {{ __('messages.apply_selected') }}
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="clearSelection()">
                {{ __('messages.clear_selection') }}
            </button>
        </div>
    </div>
</div>

<!-- Table -->
<div class="table-responsive">
    <table class="table mb-0">
        <thead class="thead-light">
            <tr>
                <th class="bulk-select-header" style="display: none;">
                    <input type="checkbox" id="select-all">
                </th>
                <th>{{ __('messages.id') }}</th>
                <th>{{ __('messages.Type') }}</th>
                <th>{{ __('messages.category') }}</th>
                <th>{{ __('messages.entity') }}</th>
                <th>{{ __('messages.amount') }}</th>
                <th>{{ __('messages.reason') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.created_at') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($finesDiscounts as $item)
            <tr>
                <td class="bulk-select-cell" style="display: none;">
                    <input type="checkbox" class="item-checkbox" value="{{ $item->id }}">
                </td>
                <td>{{ $item->id }}</td>
                <td>
                    <span class="type-badge type-fine">
                    {{ __('messages.fine') }}
                    </span>
                </td>
             
                <td>
                    <span class="category-badge category-{{ $item->category == 1 ? 'automatic' : 'manual' }}">
                        {{ $item->category_text }}
                    </span>
                </td>
                <td>
                    <div>
                        <strong>{{ $item->entity_name }}</strong>
                        <br>
                        <small class="text-muted">{{ __('messages.' . $item->entity_type) }}</small>
                        @if($item->appointment_id)
                            <br><small class="text-info">{{ __('messages.appointment') }} #{{ $item->appointment_id }}</small>
                        @endif
                    </div>
                </td>
                <td>
                    <strong>{{ number_format($item->amount, 2) }} {{ __('messages.currency') }}</strong>
                    @if($item->percentage)
                        <br><small class="text-muted">{{ $item->percentage }}%</small>
                    @endif
                </td>
                <td>
                    <div title="{{ $item->reason }}">
                        {{ Str::limit($item->reason, 30) }}
                        @if($item->notes)
                            <br><small class="text-muted">{{ Str::limit($item->notes, 25) }}</small>
                        @endif
                    </div>
                </td>
                <td>
                    <span class="status-badge status-{{ strtolower($item->status_text) }}">
                        {{ $item->status_text }}
                    </span>
                    @if($item->applied_at)
                        <br><small class="text-muted">{{ $item->applied_at->format('Y-m-d H:i') }}</small>
                    @endif
                </td>
                <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
             
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center py-5">
                    {{ __('messages.no_fines_discounts_found') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-3">
    {{ $finesDiscounts->appends(request()->query())->links() }}
</div>
@endsection
