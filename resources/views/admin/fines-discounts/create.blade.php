@extends('layouts.admin')

@section('title')
{{ __('messages.add_manual_fine_discount') }}
@endsection

@section('css')
<style>
.entity-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.entity-card.selected {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.entity-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.1);
}

.entity-info {
    display: flex;
    align-items: center;
}

.entity-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #007bff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-right: 15px;
}

.entity-details h6 {
    margin: 0;
    color: #333;
}

.entity-details small {
    color: #666;
}

.type-selector {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.type-option {
    flex: 1;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.type-option.selected {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.type-option.fine.selected {
    border-color: #dc3545;
    background-color: #fdf2f2;
}

.type-option.discount.selected {
    border-color: #28a745;
    background-color: #f2f9f2;
}

.type-icon {
    font-size: 2rem;
    margin-bottom: 10px;
}

.search-box {
    margin-bottom: 20px;
}

.balance-info {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px;
    margin-top: 10px;
}

.calculation-preview {
    background: #e9f7ef;
    border: 1px solid #c3e6cb;
    border-radius: 5px;
    padding: 15px;
    margin-top: 15px;
}

.tabs {
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 20px;
}

.tab {
    display: inline-block;
    padding: 10px 20px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    margin-right: 10px;
}

.tab.active {
    border-bottom-color: #007bff;
    color: #007bff;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}
</style>
@endsection



@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('messages.add_manual_fine_discount') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('fines-discounts.store') }}" method="POST" id="fineDiscountForm">
                    @csrf


                    <!-- Entity Type Tabs -->
                    <div class="form-group">
                        <label>{{ __('messages.select_entity_type') }} <span class="text-danger">*</span></label>
                        <div class="tabs">
                            <button type="button" class="tab {{ old('entity_type', $preselected['entity_type']) == 'user' || !old('entity_type') ? 'active' : '' }}" 
                                    onclick="switchTab('user')">
                                {{ __('messages.users') }}
                            </button>
                            <button type="button" class="tab {{ old('entity_type', $preselected['entity_type']) == 'provider' ? 'active' : '' }}" 
                                    onclick="switchTab('provider')">
                                {{ __('messages.providers') }}
                            </button>
                        </div>
                        <input type="hidden" name="entity_type" id="entity_type" value="{{ old('entity_type', $preselected['entity_type'] ?: 'user') }}">
                        @error('entity_type')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Users Tab -->
                    <div id="user-tab" class="tab-content {{ old('entity_type', $preselected['entity_type']) == 'user' || !old('entity_type') ? 'active' : '' }}">
                        <div class="search-box">
                            <input type="text" class="form-control" placeholder="{{ __('messages.search_users') }}" 
                                   onkeyup="searchEntities('user', this.value)">
                        </div>
                        <div id="user-list">
                            @foreach($users as $user)
                            <div class="entity-card user-entity {{ old('entity_id', $preselected['entity_id']) == $user->id ? 'selected' : '' }}" 
                                 onclick="selectEntity('user', {{ $user->id }}, '{{ $user->name }}', {{ $user->balance }})">
                                <div class="entity-info">
                                    <div class="entity-avatar">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div class="entity-details">
                                        <h6>{{ $user->name }}</h6>
                                        <small>{{ $user->phone }} • {{ __('messages.balance') }}: {{ number_format($user->balance, 2) }} {{ __('messages.currency') }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Providers Tab -->
                    <div id="provider-tab" class="tab-content {{ old('entity_type', $preselected['entity_type']) == 'provider' ? 'active' : '' }}">
                        <div class="search-box">
                            <input type="text" class="form-control" placeholder="{{ __('messages.search_providers') }}" 
                                   onkeyup="searchEntities('provider', this.value)">
                        </div>
                        <div id="provider-list">
                            @foreach($providers as $provider)
                            <div class="entity-card provider-entity {{ old('entity_id', $preselected['entity_id']) == $provider->id ? 'selected' : '' }}" 
                                 onclick="selectEntity('provider', {{ $provider->id }}, '{{ $provider->name_of_manager }}', {{ $provider->balance }})">
                                <div class="entity-info">
                                    <div class="entity-avatar">
                                        {{ substr($provider->name_of_manager, 0, 1) }}
                                    </div>
                                    <div class="entity-details">
                                        <h6>{{ $provider->name_of_manager }}</h6>
                                        <small>{{ $provider->phone }} • {{ __('messages.balance') }}: {{ number_format($provider->balance, 2) }} {{ __('messages.currency') }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <input type="hidden" name="entity_id" id="entity_id" value="{{ old('entity_id', $preselected['entity_id']) }}">
                    @error('entity_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror

                    <!-- Selected Entity Info -->
                    <div id="selected-entity-info" class="balance-info" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>{{ __('messages.selected') }}:</strong> <span id="selected-name"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>{{ __('messages.current_balance') }}:</strong> 
                                <span id="selected-balance"></span> {{ __('messages.currency') }}
                            </div>
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="form-group">
                        <label for="amount">{{ __('messages.amount') }} ({{ __('messages.currency') }}) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="amount" 
                               class="form-control @error('amount') is-invalid @enderror" 
                               value="{{ old('amount') }}" step="0.01" min="0.01" required
                               oninput="updatePreview()">
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Reason -->
                    <div class="form-group">
                        <label for="reason">{{ __('messages.reason') }} <span class="text-danger">*</span></label>
                        <input type="text" name="reason" id="reason" 
                               class="form-control @error('reason') is-invalid @enderror" 
                               value="{{ old('reason') }}" required maxlength="255"
                               placeholder="{{ __('messages.enter_reason') }}">
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="notes">{{ __('messages.additional_notes') }}</label>
                        <textarea name="notes" id="notes" rows="3" 
                                  class="form-control @error('notes') is-invalid @enderror" 
                                  placeholder="{{ __('messages.enter_additional_notes') }}">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Apply Immediately -->
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="apply_immediately" 
                                   name="apply_immediately" value="1" {{ old('apply_immediately') ? 'checked' : '' }}>
                            <label class="form-check-label" for="apply_immediately">
                                {{ __('messages.apply_immediately') }}
                            </label>
                            <small class="form-text text-muted">
                                {{ __('messages.apply_immediately_help') }}
                            </small>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div id="calculation-preview" class="calculation-preview" style="display: none;">
                        <h6>{{ __('messages.preview') }}</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>{{ __('messages.current_balance') }}:</strong><br>
                                <span id="preview-current-balance">0.00</span> {{ __('messages.currency') }}
                            </div>
                            <div class="col-md-4">
                                <strong>{{ __('messages.amount') }}:</strong><br>
                                <span id="preview-amount-sign">+</span><span id="preview-amount">0.00</span> {{ __('messages.currency') }}
                            </div>
                            <div class="col-md-4">
                                <strong>{{ __('messages.new_balance') }}:</strong><br>
                                <span id="preview-new-balance">0.00</span> {{ __('messages.currency') }}
                            </div>
                        </div>
                        <div class="mt-2" id="negative-balance-warning" style="display: none;">
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ __('messages.negative_balance_warning') }}
                            </small>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> {{ __('messages.create') }}
                        </button>
                        <a href="{{ route('fines-discounts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
let selectedEntityBalance = 0;

function selectType(type) {
    document.getElementById('type').value = type;
    
    // Update UI
    document.querySelectorAll('.type-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    if (type == 1) {
        document.querySelector('.type-option.fine').classList.add('selected');
    } else {
        document.querySelector('.type-option.discount').classList.add('selected');
    }
    
    updatePreview();
}

function switchTab(entityType) {
    document.getElementById('entity_type').value = entityType;
    
    // Update tabs
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[onclick="switchTab('${entityType}')"]`).classList.add('active');
    
    // Update content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(`${entityType}-tab`).classList.add('active');
    
    // Clear selection
    clearEntitySelection();
}

function selectEntity(entityType, entityId, entityName, entityBalance) {
    document.getElementById('entity_id').value = entityId;
    selectedEntityBalance = entityBalance;
    
    // Update UI
    document.querySelectorAll('.entity-card').forEach(card => {
        card.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
    
    // Show selected entity info
    document.getElementById('selected-name').textContent = entityName;
    document.getElementById('selected-balance').textContent = parseFloat(entityBalance).toFixed(2);
    document.getElementById('selected-entity-info').style.display = 'block';
    
    updatePreview();
}

function clearEntitySelection() {
    document.getElementById('entity_id').value = '';
    selectedEntityBalance = 0;
    document.querySelectorAll('.entity-card').forEach(card => {
        card.classList.remove('selected');
    });
    document.getElementById('selected-entity-info').style.display = 'none';
    document.getElementById('calculation-preview').style.display = 'none';
}

function searchEntities(entityType, searchTerm) {
    const entities = document.querySelectorAll(`.${entityType}-entity`);
    
    entities.forEach(entity => {
        const text = entity.textContent.toLowerCase();
        if (text.includes(searchTerm.toLowerCase())) {
            entity.style.display = 'block';
        } else {
            entity.style.display = 'none';
        }
    });
}

function updatePreview() {
    const entityId = document.getElementById('entity_id').value;
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const type = document.getElementById('type').value;
    
    if (!entityId || amount <= 0) {
        document.getElementById('calculation-preview').style.display = 'none';
        return;
    }
    
    const currentBalance = selectedEntityBalance;
    const isDebit = type == 1; // Fine
    const newBalance = isDebit ? currentBalance - amount : currentBalance + amount;
    
    // Update preview
    document.getElementById('preview-current-balance').textContent = currentBalance.toFixed(2);
    document.getElementById('preview-amount-sign').textContent = isDebit ? '-' : '+';
    document.getElementById('preview-amount').textContent = amount.toFixed(2);
    document.getElementById('preview-new-balance').textContent = newBalance.toFixed(2);
    
    // Show/hide negative balance warning
    const warningDiv = document.getElementById('negative-balance-warning');
    if (newBalance < 0) {
        warningDiv.style.display = 'block';
    } else {
        warningDiv.style.display = 'none';
    }
    
    document.getElementById('calculation-preview').style.display = 'block';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // If we have preselected values (from URL parameters), show the entity info
    const entityId = document.getElementById('entity_id').value;
    if (entityId) {
        const selectedCard = document.querySelector(`.entity-card.selected`);
        if (selectedCard) {
            // Extract balance from the card
            const balanceText = selectedCard.querySelector('.entity-details small').textContent;
            const balanceMatch = balanceText.match(/[\d,]+\.?\d*/);
            if (balanceMatch) {
                selectedEntityBalance = parseFloat(balanceMatch[0].replace(/,/g, ''));
                const nameElement = selectedCard.querySelector('.entity-details h6');
                if (nameElement) {
                    document.getElementById('selected-name').textContent = nameElement.textContent;
                    document.getElementById('selected-balance').textContent = selectedEntityBalance.toFixed(2);
                    document.getElementById('selected-entity-info').style.display = 'block';
                }
            }
        }
    }
    
    updatePreview();
});
</script>
@endsection