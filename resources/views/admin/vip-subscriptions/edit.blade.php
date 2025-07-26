@extends('layouts.admin')

@section('title')
{{ __('messages.edit_vip_subscription') }}
@endsection



@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('messages.edit_subscription') }} #{{ $vipSubscription->id }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.vip-subscriptions.update', $vipSubscription) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="provider_type_id">{{ __('messages.select_provider_salon') }} <span class="text-danger">*</span></label>
                        <select name="provider_type_id" id="provider_type_id" class="form-control @error('provider_type_id') is-invalid @enderror" required>
                            <option value="">{{ __('messages.select_option') }}</option>
                            @foreach($providerTypes as $providerType)
                                <option value="{{ $providerType->id }}" 
                                        {{ (old('provider_type_id', $vipSubscription->provider_type_id) == $providerType->id) ? 'selected' : '' }}>
                                    {{ $providerType->provider->name_of_manager }} - {{ $providerType->name }} 
                                    ({{ $providerType->type->name_ar }})
                                </option>
                            @endforeach
                        </select>
                        @error('provider_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">{{ __('messages.start_date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date', $vipSubscription->start_date->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">{{ __('messages.end_date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="end_date" 
                                       class="form-control @error('end_date') is-invalid @enderror" 
                                       value="{{ old('end_date', $vipSubscription->end_date->format('Y-m-d')) }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="amount_paid">{{ __('messages.amount_paid') }} ({{ __('messages.currency') }}) <span class="text-danger">*</span></label>
                        <input type="number" name="amount_paid" id="amount_paid" 
                               class="form-control @error('amount_paid') is-invalid @enderror" 
                               value="{{ old('amount_paid', $vipSubscription->amount_paid) }}" step="0.01" min="0" required>
                        @error('amount_paid')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">{{ __('messages.subscription_status') }} <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="1" {{ old('status', $vipSubscription->status) == '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                    <option value="2" {{ old('status', $vipSubscription->status) == '2' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                                    <option value="3" {{ old('status', $vipSubscription->status) == '3' ? 'selected' : '' }}>{{ __('messages.expired') }}</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_status">{{ __('messages.payment_status') }} <span class="text-danger">*</span></label>
                                <select name="payment_status" id="payment_status" class="form-control @error('payment_status') is-invalid @enderror" required>
                                    <option value="1" {{ old('payment_status', $vipSubscription->payment_status) == '1' ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
                                    <option value="2" {{ old('payment_status', $vipSubscription->payment_status) == '2' ? 'selected' : '' }}>{{ __('messages.unpaid') }}</option>
                                </select>
                                @error('payment_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">{{ __('messages.payment_method') }}</label>
                        <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror">
                            <option value="">{{ __('messages.select_option') }}</option>
                            <option value="cash" {{ old('payment_method', $vipSubscription->payment_method) == 'cash' ? 'selected' : '' }}>{{ __('messages.cash') }}</option>
                            <option value="visa" {{ old('payment_method', $vipSubscription->payment_method) == 'visa' ? 'selected' : '' }}>{{ __('messages.visa') }}</option>
                            <option value="wallet" {{ old('payment_method', $vipSubscription->payment_method) == 'wallet' ? 'selected' : '' }}>{{ __('messages.wallet') }}</option>
                            <option value="bank_transfer" {{ old('payment_method', $vipSubscription->payment_method) == 'bank_transfer' ? 'selected' : '' }}>{{ __('messages.bank_transfer') }}</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="notes">{{ __('messages.notes') }}</label>
                        <textarea name="notes" id="notes" rows="4" 
                                  class="form-control @error('notes') is-invalid @enderror" 
                                  placeholder="{{ __('messages.enter_notes') }}">{{ old('notes', $vipSubscription->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> {{ __('messages.update') }}
                        </button>
                        <a href="{{ route('admin.vip-subscriptions.show', $vipSubscription) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> {{ __('messages.view') }}
                        </a>
                        <a href="{{ route('admin.vip-subscriptions.index') }}" class="btn btn-secondary">
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
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDateInput = document.getElementById('end_date');
    
    // Set minimum end date to be after start date
    if (startDate) {
        const nextDay = new Date(startDate);
        nextDay.setDate(nextDay.getDate() + 1);
        endDateInput.min = nextDay.toISOString().split('T')[0];
        
        // If end date is before start date, clear it
        if (endDateInput.value && new Date(endDateInput.value) <= startDate) {
            endDateInput.value = '';
        }
    }
});
</script>
@endsection