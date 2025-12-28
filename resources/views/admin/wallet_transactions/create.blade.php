@extends('layouts.admin')

@section('title', __('messages.Create_Transaction'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Create_Transaction') }}</h1>
        <a href="{{ route('wallet_transactions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
        </a>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Transaction_Details') }}</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('wallet_transactions.store') }}" method="POST" id="transaction-form">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Entity Selection -->
                        <div class="form-group">
                            <label for="entity_type">{{ __('messages.Entity_Type') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="entity_type" name="entity_type" required>
                                <option value="">{{ __('messages.Select_Entity_Type') }}</option>
                                <option value="user" {{ old('entity_type') == 'user' ? 'selected' : '' }}>{{ __('messages.User') }}</option>
                                <option value="provider" {{ old('entity_type') == 'provider' ? 'selected' : '' }}>{{ __('messages.provider') }}</option>
                            </select>
                        </div>
                        
                        <!-- User Selection -->
                        <div class="form-group entity-select user-select" style="display: {{ old('entity_type') == 'user' ? 'block' : 'none' }};">
                            <label for="user_entity_id">{{ __('messages.Select_User') }} <span class="text-danger">*</span></label>
                            <select class="form-control entity-id-select" id="user_entity_id" data-entity-type="user">
                                <option value="">{{ __('messages.Select_User') }}</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" 
                                        {{ old('entity_id') == $user->id && old('entity_type') == 'user' ? 'selected' : '' }} 
                                        data-balance="{{ $user->balance }}">
                                    {{ $user->name }} ({{ $user->phone }}) - {{ __('messages.Balance') }}: {{ number_format($user->balance, 2) }} JD
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Provider Selection -->
                        <div class="form-group entity-select provider-select" style="display: {{ old('entity_type') == 'provider' ? 'block' : 'none' }};">
                            <label for="provider_entity_id">{{ __('messages.Select_provider') }} <span class="text-danger">*</span></label>
                            <select class="form-control entity-id-select" id="provider_entity_id" data-entity-type="provider">
                                <option value="">{{ __('messages.Select_provider') }}</option>
                                @foreach($providers as $provider)
                                <option value="{{ $provider->id }}" 
                                        {{ old('entity_id') == $provider->id && old('entity_type') == 'provider' ? 'selected' : '' }} 
                                        data-balance="{{ $provider->balance }}">
                                    {{ $provider->providerTypes->first()?->name ?? 'N/A' }} ({{ $provider->phone }}) - {{ __('messages.Balance') }}: {{ number_format($provider->balance, 2) }} JD
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Hidden input to hold the actual entity_id -->
                        <input type="hidden" id="entity_id" name="entity_id" value="{{ old('entity_id') }}">
                        
                        <div class="alert alert-info current-balance mt-3" style="display: none;">
                            <strong>{{ __('messages.Current_Balance') }}:</strong> <span id="balance-amount">0</span> JD
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Transaction Details -->
                        <div class="form-group">
                            <label for="type_of_transaction">{{ __('messages.Transaction_Type') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="type_of_transaction" name="type_of_transaction" required>
                                <option value="1" {{ old('type_of_transaction', 1) == 1 ? 'selected' : '' }}>
                                    <i class="fas fa-plus-circle"></i> {{ __('messages.Deposit') }}
                                </option>
                                <option value="2" {{ old('type_of_transaction') == 2 ? 'selected' : '' }}>
                                    <i class="fas fa-minus-circle"></i> {{ __('messages.Withdrawal') }}
                                </option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount">{{ __('messages.Amount') }} (JD) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   step="0.01" 
                                   class="form-control" 
                                   id="amount" 
                                   name="amount" 
                                   value="{{ old('amount') }}" 
                                   required 
                                   min="0.01"
                                   placeholder="0.00">
                            <div class="text-danger mt-1 font-weight-bold" id="balance-warning" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i> {{ __('messages.Insufficient_Balance_Warning') }}
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="note">{{ __('messages.Note') }}</label>
                            <textarea class="form-control" 
                                      id="note" 
                                      name="note" 
                                      rows="4"
                                      placeholder="{{ __('messages.Enter_transaction_note') }}">{{ old('note') }}</textarea>
                            <small class="form-text text-muted">{{ __('messages.Transaction_Note_Info') }}</small>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg submit-btn px-5">
                        <i class="fas fa-save"></i> {{ __('messages.Save_Transaction') }}
                    </button>
                    <a href="{{ route('wallet_transactions.index') }}" class="btn btn-secondary btn-lg px-5">
                        <i class="fas fa-times"></i> {{ __('messages.Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Handle entity type selection
        $('#entity_type').on('change', function() {
            $('.entity-select').hide();
            $('.current-balance').hide();
            $('#entity_id').val(''); // Clear hidden input
            $('#balance-warning').hide();
            
            if ($(this).val() == 'user') {
                $('.user-select').show();
                $('#provider_entity_id').val('');
                
                // Set hidden input from user select if already selected
                var userValue = $('#user_entity_id').val();
                if (userValue) {
                    $('#entity_id').val(userValue);
                    $('#user_entity_id').trigger('change');
                }
            } else if ($(this).val() == 'provider') {
                $('.provider-select').show();
                $('#user_entity_id').val('');
                
                // Set hidden input from provider select if already selected
                var providerValue = $('#provider_entity_id').val();
                if (providerValue) {
                    $('#entity_id').val(providerValue);
                    $('#provider_entity_id').trigger('change');
                }
            }
        });
        
        // Trigger initial state
        if ($('#entity_type').val()) {
            $('#entity_type').trigger('change');
        }
        
        // Handle user selection
        $('#user_entity_id').on('change', function() {
            var value = $(this).val();
            $('#entity_id').val(value); // Update hidden input
            
            if (value) {
                var balance = $(this).find('option:selected').data('balance');
                $('#balance-amount').text(parseFloat(balance).toFixed(2));
                $('.current-balance').show();
                checkBalance();
            } else {
                $('.current-balance').hide();
                $('#balance-warning').hide();
            }
        });
        
        // Handle provider selection
        $('#provider_entity_id').on('change', function() {
            var value = $(this).val();
            $('#entity_id').val(value); // Update hidden input
            
            if (value) {
                var balance = $(this).find('option:selected').data('balance');
                $('#balance-amount').text(parseFloat(balance).toFixed(2));
                $('.current-balance').show();
                checkBalance();
            } else {
                $('.current-balance').hide();
                $('#balance-warning').hide();
            }
        });
        
        // Handle transaction type and amount changes
        $('#type_of_transaction, #amount').on('change keyup input', function() {
            checkBalance();
        });
        
        // Check if balance is sufficient for withdrawal
        function checkBalance() {
            var transactionType = $('#type_of_transaction').val();
            var amount = parseFloat($('#amount').val()) || 0;
            var balance = parseFloat($('#balance-amount').text()) || 0;
            
            if (transactionType == '2' && amount > balance) {
                $('#balance-warning').show();
                $('.submit-btn').prop('disabled', true);
                $('.submit-btn').addClass('disabled');
            } else {
                $('#balance-warning').hide();
                $('.submit-btn').prop('disabled', false);
                $('.submit-btn').removeClass('disabled');
            }
        }
        
        // Validate before form submission
        $('#transaction-form').on('submit', function(e) {
            var entityType = $('#entity_type').val();
            var entityId = $('#entity_id').val();
            var amount = parseFloat($('#amount').val()) || 0;
            
            if (!entityType) {
                e.preventDefault();
                alert('Please select an entity type (User or Provider)');
                $('#entity_type').focus();
                return false;
            }
            
            if (!entityId) {
                e.preventDefault();
                var entityName = entityType == 'user' ? 'user' : 'provider';
                alert('Please select a ' + entityName);
                if (entityType == 'user') {
                    $('#user_entity_id').focus();
                } else {
                    $('#provider_entity_id').focus();
                }
                return false;
            }
            
            if (amount <= 0) {
                e.preventDefault();
                alert('Please enter a valid amount greater than 0');
                $('#amount').focus();
                return false;
            }
            
            // Check balance one more time before submission
            var transactionType = $('#type_of_transaction').val();
            var balance = parseFloat($('#balance-amount').text()) || 0;
            
            if (transactionType == '2' && amount > balance) {
                e.preventDefault();
                alert('Insufficient balance! Available balance: ' + balance.toFixed(2) + ' JD');
                return false;
            }
            
            // Show loading state
            $('.submit-btn').prop('disabled', true);
            $('.submit-btn').html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            
            return true;
        });

        // Format amount on blur
        $('#amount').on('blur', function() {
            var value = parseFloat($(this).val());
            if (!isNaN(value)) {
                $(this).val(value.toFixed(2));
            }
        });
    });
</script>

<style>
    .current-balance {
        animation: fadeIn 0.3s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    #balance-warning {
        animation: shake 0.5s;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    .submit-btn.disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
</style>
@endsection