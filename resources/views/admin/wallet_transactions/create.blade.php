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

            <form action="{{ route('wallet_transactions.store') }}" method="POST">
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
                            <select class="form-control" id="user_entity_id" name="entity_id">
                                <option value="">{{ __('messages.Select_User') }}</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('entity_id') == $user->id && old('entity_type') == 'user' ? 'selected' : '' }} data-balance="{{ $user->balance }}">
                                    {{ $user->name }} ({{ $user->phone }}) - {{ __('messages.Balance') }}: {{ $user->balance }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- provider Selection -->
                        <div class="form-group entity-select provider-select" style="display: {{ old('entity_type') == 'provider' ? 'block' : 'none' }};">
                            <label for="provider_entity_id">{{ __('messages.Select_provider') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="provider_entity_id" name="entity_id">
                                <option value="">{{ __('messages.Select_provider') }}</option>
                                @foreach($providers as $provider)
                                <option value="{{ $provider->id }}" {{ old('entity_id') == $provider->id && old('entity_type') == 'provider' ? 'selected' : '' }} data-balance="{{ $provider->balance }}">
                                    {{ $provider->providerTypes->first()->name }}  ({{ $provider->phone }}) - {{ __('messages.Balance') }}: {{ $provider->balance }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="alert alert-info current-balance mt-3" style="display: none;">
                            <strong>{{ __('messages.Current_Balance') }}:</strong> <span id="balance-amount">0</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Transaction Details -->
                        <div class="form-group">
                            <label for="type_of_transaction">{{ __('messages.Transaction_Type') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="type_of_transaction" name="type_of_transaction" required>
                                <option value="1" {{ old('type_of_transaction', 1) == 1 ? 'selected' : '' }}>{{ __('messages.Deposit') }}</option>
                                <option value="2" {{ old('type_of_transaction') == 2 ? 'selected' : '' }}>{{ __('messages.Withdrawal') }}</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount">{{ __('messages.Amount') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required min="0.01">
                            <div class="text-danger mt-1" id="balance-warning" style="display: none;">
                                {{ __('messages.Insufficient_Balance_Warning') }}
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="note">{{ __('messages.Note') }}</label>
                            <textarea class="form-control" id="note" name="note" rows="3">{{ old('note') }}</textarea>
                            <small class="form-text text-muted">{{ __('messages.Transaction_Note_Info') }}</small>
                        </div>
                    </div>
                </div>

                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-primary submit-btn">
                        <i class="fas fa-save"></i> {{ __('messages.Save_Transaction') }}
                    </button>
                    <a href="{{ route('wallet_transactions.index') }}" class="btn btn-secondary">
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
            
            if ($(this).val() == 'user') {
                $('.user-select').show();
                $('#provider_entity_id').val('');
                $('#user_entity_id').trigger('change');
            } else if ($(this).val() == 'provider') {
                $('.provider-select').show();
                $('#user_entity_id').val('');
                $('#provider_entity_id').trigger('change');
            }
        });
        
        // Trigger initial state
        $('#entity_type').trigger('change');
        
        // Handle user selection
        $('#user_entity_id').on('change', function() {
            if ($(this).val()) {
                var balance = $(this).find('option:selected').data('balance');
                $('#balance-amount').text(balance);
                $('.current-balance').show();
                checkBalance();
            } else {
                $('.current-balance').hide();
            }
        });
        
        // Handle provider selection
        $('#provider_entity_id').on('change', function() {
            if ($(this).val()) {
                var balance = $(this).find('option:selected').data('balance');
                $('#balance-amount').text(balance);
                $('.current-balance').show();
                checkBalance();
            } else {
                $('.current-balance').hide();
            }
        });
        
        // Handle transaction type and amount changes
        $('#type_of_transaction, #amount').on('change', function() {
            checkBalance();
        });
        
        // Check if balance is sufficient for withdrawal
        function checkBalance() {
            var transactionType = $('#type_of_transaction').val();
            var amount = parseFloat($('#amount').val()) || 0;
            var balance = parseFloat($('#balance-amount').text()) || 0;
            
            if (transactionType == '2' && amount > balance) {
                $('#balance-warning').show();
                $('.submit-btn').attr('disabled', true);
            } else {
                $('#balance-warning').hide();
                $('.submit-btn').attr('disabled', false);
            }
        }
    });
</script>
@endsection