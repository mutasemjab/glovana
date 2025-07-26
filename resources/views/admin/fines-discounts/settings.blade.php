@extends('layouts.admin')

@section('title')
{{ __('messages.fine_settings') }}
@endsection

@section('css')
<style>
.settings-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.setting-group {
    border-bottom: 1px solid #f0f0f0;
    padding: 20px;
}

.setting-group:last-child {
    border-bottom: none;
}

.setting-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.setting-description {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.preview-section {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
}

.example-calculation {
    background: #e9f7ef;
    border: 1px solid #c3e6cb;
    border-radius: 5px;
    padding: 15px;
    margin-top: 10px;
}

.warning-box {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 5px;
    padding: 15px;
    margin-top: 15px;
}

.status-indicator {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
}

.status-enabled { background: #28a745; }
.status-disabled { background: #dc3545; }
</style>
@endsection



@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="settings-card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('messages.fine_and_discount_settings') }}</h5>
                <small class="text-muted">{{ __('messages.configure_automatic_fine_rules') }}</small>
            </div>
            
            <form action="{{ route('fines-discounts.update-settings') }}" method="POST">
                @csrf
                
                <!-- Late Cancellation Hours -->
                <div class="setting-group">
                    <div class="setting-label">{{ __('messages.late_cancellation_threshold') }}</div>
                    <div class="setting-description">
                        {{ __('messages.late_cancellation_threshold_desc') }}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="number" name="late_cancellation_hours" 
                                       class="form-control @error('late_cancellation_hours') is-invalid @enderror" 
                                       value="{{ old('late_cancellation_hours', $settings['late_cancellation_hours'] ?? 24) }}" 
                                       min="1" max="168" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">{{ __('messages.hours') }}</span>
                                </div>
                            </div>
                            @error('late_cancellation_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Fine Percentage -->
                <div class="setting-group">
                    <div class="setting-label">{{ __('messages.fine_percentage') }}</div>
                    <div class="setting-description">
                        {{ __('messages.fine_percentage_desc') }}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="number" name="fine_percentage" 
                                       class="form-control @error('fine_percentage') is-invalid @enderror" 
                                       value="{{ old('fine_percentage', $settings['fine_percentage'] ?? 25) }}" 
                                       min="0" max="100" step="0.1" required
                                       oninput="updateCalculationPreview()">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            @error('fine_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Minimum Fine Amount -->
                <div class="setting-group">
                    <div class="setting-label">{{ __('messages.minimum_fine_amount') }}</div>
                    <div class="setting-description">
                        {{ __('messages.minimum_fine_amount_desc') }}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="number" name="minimum_fine_amount" 
                                       class="form-control @error('minimum_fine_amount') is-invalid @enderror" 
                                       value="{{ old('minimum_fine_amount', $settings['minimum_fine_amount'] ?? 5) }}" 
                                       min="0" step="0.01" required
                                       oninput="updateCalculationPreview()">
                                <div class="input-group-append">
                                    <span class="input-group-text">{{ __('messages.currency') }}</span>
                                </div>
                            </div>
                            @error('minimum_fine_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Maximum Fine Amount -->
                <div class="setting-group">
                    <div class="setting-label">{{ __('messages.maximum_fine_amount') }}</div>
                    <div class="setting-description">
                        {{ __('messages.maximum_fine_amount_desc') }}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="number" name="maximum_fine_amount" 
                                       class="form-control @error('maximum_fine_amount') is-invalid @enderror" 
                                       value="{{ old('maximum_fine_amount', $settings['maximum_fine_amount'] ?? 100) }}" 
                                       min="0" step="0.01" required
                                       oninput="updateCalculationPreview()">
                                <div class="input-group-append">
                                    <span class="input-group-text">{{ __('messages.currency') }}</span>
                                </div>
                            </div>
                            @error('maximum_fine_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Auto Apply Fines -->
                <div class="setting-group">
                    <div class="setting-label">{{ __('messages.automatic_fine_application') }}</div>
                    <div class="setting-description">
                        {{ __('messages.automatic_fine_application_desc') }}
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="auto_apply_fines" 
                                       id="auto_apply_yes" value="1" 
                                       {{ old('auto_apply_fines', $settings['auto_apply_fines'] ?? 1) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_apply_yes">
                                    <span class="status-indicator status-enabled"></span>
                                    {{ __('messages.enabled') }}
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="auto_apply_fines" 
                                       id="auto_apply_no" value="2" 
                                       {{ old('auto_apply_fines', $settings['auto_apply_fines'] ?? 1) == '2' ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_apply_no">
                                    <span class="status-indicator status-disabled"></span>
                                    {{ __('messages.disabled') }}
                                </label>
                            </div>
                            @error('auto_apply_fines')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Allow Negative Balance -->
                <div class="setting-group">
                    <div class="setting-label">{{ __('messages.allow_negative_balance') }}</div>
                    <div class="setting-description">
                        {{ __('messages.allow_negative_balance_desc') }}
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="allow_negative_balance" 
                                       id="negative_yes" value="1" 
                                       {{ old('allow_negative_balance', $settings['allow_negative_balance'] ?? 2) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="negative_yes">
                                    <span class="status-indicator status-enabled"></span>
                                    {{ __('messages.allowed') }}
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="allow_negative_balance" 
                                       id="negative_no" value="2" 
                                       {{ old('allow_negative_balance', $settings['allow_negative_balance'] ?? 2) == '2' ? 'checked' : '' }}>
                                <label class="form-check-label" for="negative_no">
                                    <span class="status-indicator status-disabled"></span>
                                    {{ __('messages.not_allowed') }}
                                </label>
                            </div>
                            @error('allow_negative_balance')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="setting-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> {{ __('messages.save_settings') }}
                    </button>
                    <a href="{{ route('fines-discounts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                </div>
            </form>
        </div>

        <!-- Preview Section -->
        <div class="preview-section">
            <h6>{{ __('messages.calculation_preview') }}</h6>
            <p class="text-muted">{{ __('messages.see_how_fines_calculated') }}</p>
            
            <div class="row">
                <div class="col-md-6">
                    <label>{{ __('messages.appointment_total') }}:</label>
                    <div class="input-group">
                        <input type="number" id="sample_amount" class="form-control" 
                               value="50" min="0" step="0.01" oninput="updateCalculationPreview()">
                        <div class="input-group-append">
                            <span class="input-group-text">{{ __('messages.currency') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label>{{ __('messages.hours_before_appointment') }}:</label>
                    <div class="input-group">
                        <input type="number" id="sample_hours" class="form-control" 
                               value="12" min="0" step="1" oninput="updateCalculationPreview()">
                        <div class="input-group-append">
                            <span class="input-group-text">{{ __('messages.hours') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="example-calculation" id="calculation-result">
                <!-- Dynamic calculation will be inserted here -->
            </div>

            <div class="warning-box" id="warning-box" style="display: none;">
                <i class="fas fa-exclamation-triangle text-warning"></i>
                <strong>{{ __('messages.warning') }}:</strong>
                <span id="warning-text"></span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
function updateCalculationPreview() {
    const sampleAmount = parseFloat(document.getElementById('sample_amount').value) || 0;
    const sampleHours = parseFloat(document.getElementById('sample_hours').value) || 0;
    const threshold = parseFloat(document.querySelector('[name="late_cancellation_hours"]').value) || 24;
    const percentage = parseFloat(document.querySelector('[name="fine_percentage"]').value) || 25;
    const minFine = parseFloat(document.querySelector('[name="minimum_fine_amount"]').value) || 5;
    const maxFine = parseFloat(document.querySelector('[name="maximum_fine_amount"]').value) || 100;

    const resultDiv = document.getElementById('calculation-result');
    const warningBox = document.getElementById('warning-box');
    const warningText = document.getElementById('warning-text');

    // Check if validation errors exist
    let hasErrors = false;
    let errorMessage = '';

    if (minFine > maxFine) {
        hasErrors = true;
        errorMessage = '{{ __('messages.minimum_must_be_less_than_maximum') }}';
    }

    if (hasErrors) {
        warningBox.style.display = 'block';
        warningText.textContent = errorMessage;
        resultDiv.innerHTML = '<div class="text-danger">{{ __('messages.fix_errors_to_see_preview') }}</div>';
        return;
    } else {
        warningBox.style.display = 'none';
    }

    let html = '<h6>{{ __('messages.scenario') }}:</h6>';
    html += '<p>{{ __('messages.appointment_worth') }}: <strong>' + sampleAmount.toFixed(2) + ' {{ __('messages.currency') }}</strong></p>';
    html += '<p>{{ __('messages.canceled') }}: <strong>' + sampleHours + ' {{ __('messages.hours_before') }}</strong></p>';

    if (sampleHours <= threshold) {
        // Fine will be applied
        let fineAmount = (sampleAmount * percentage) / 100;
        fineAmount = Math.max(minFine, Math.min(maxFine, fineAmount));

        html += '<div class="mt-3 text-danger">';
        html += '<h6><i class="fas fa-exclamation-circle"></i> {{ __('messages.fine_will_be_applied') }}</h6>';
        html += '<ul>';
        html += '<li>{{ __('messages.base_calculation') }}: ' + sampleAmount.toFixed(2) + ' × ' + percentage + '% = ' + ((sampleAmount * percentage) / 100).toFixed(2) + ' {{ __('messages.currency') }}</li>';
        
        if (((sampleAmount * percentage) / 100) < minFine) {
            html += '<li>{{ __('messages.applied_minimum') }}: ' + minFine.toFixed(2) + ' {{ __('messages.currency') }}</li>';
        } else if (((sampleAmount * percentage) / 100) > maxFine) {
            html += '<li>{{ __('messages.applied_maximum') }}: ' + maxFine.toFixed(2) + ' {{ __('messages.currency') }}</li>';
        }
        
        html += '<li><strong>{{ __('messages.final_fine_amount') }}: ' + fineAmount.toFixed(2) + ' {{ __('messages.currency') }}</strong></li>';
        html += '</ul>';
        html += '</div>';
    } else {
        // No fine
        html += '<div class="mt-3 text-success">';
        html += '<h6><i class="fas fa-check-circle"></i> {{ __('messages.no_fine_applied') }}</h6>';
        html += '<p>{{ __('messages.cancellation_within_allowed_time') }}</p>';
        html += '</div>';
    }

    resultDiv.innerHTML = html;
}

// Initialize preview on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCalculationPreview();

    // Add event listeners to all inputs that affect calculation
    document.querySelectorAll('[name="late_cancellation_hours"], [name="fine_percentage"], [name="minimum_fine_amount"], [name="maximum_fine_amount"]').forEach(input => {
        input.addEventListener('input', updateCalculationPreview);
    });
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const minFine = parseFloat(document.querySelector('[name="minimum_fine_amount"]').value) || 0;
    const maxFine = parseFloat(document.querySelector('[name="maximum_fine_amount"]').value) || 0;

    if (minFine > maxFine) {
        e.preventDefault();
        alert('{{ __('messages.minimum_must_be_less_than_maximum') }}');
        return false;
    }
});
</script>
@endsection