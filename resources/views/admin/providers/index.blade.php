@extends('layouts.admin')

@section('title', __('messages.providers'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.providers') }}</h1>
        <a href="{{ route('providers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.Add_New_provider') }}
        </a>
    </div>

    <!-- Filter Form -->
   <!-- Filter Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Filters') }}</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('providers.index') }}" class="row">
            <div class="col-md-3 mb-3">
                <label for="status">{{ __('messages.Status') }}</label>
                <select name="status" id="status" class="form-control">
                    <option value="">{{ __('messages.All_Status') }}</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('messages.Active') }}</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('messages.Inactive') }}</option>
                </select>
            </div>
            
            <!-- NEW: Provider Type Filter -->
            <div class="col-md-3 mb-3">
                <label for="type_id">{{ __('messages.Provider_Type') }}</label>
                <select name="type_id" id="type_id" class="form-control">
                    <option value="">{{ __('messages.All_Types') }}</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="balance_type">{{ __('messages.Balance_Type') }}</label>
                <select name="balance_type" id="balance_type" class="form-control">
                    <option value="">{{ __('messages.All_Balances') }}</option>
                    <option value="positive" {{ request('balance_type') == 'positive' ? 'selected' : '' }}>{{ __('messages.Positive_Balance') }}</option>
                    <option value="negative" {{ request('balance_type') == 'negative' ? 'selected' : '' }}>{{ __('messages.Negative_Balance') }}</option>
                    <option value="zero" {{ request('balance_type') == 'zero' ? 'selected' : '' }}>{{ __('messages.Zero_Balance') }}</option>
                </select>
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="min_balance">{{ __('messages.Min_Balance') }}</label>
                <input type="number" name="min_balance" id="min_balance" class="form-control" 
                       value="{{ request('min_balance') }}" step="0.01" placeholder="0.00">
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="max_balance">{{ __('messages.Max_Balance') }}</label>
                <input type="number" name="max_balance" id="max_balance" class="form-control" 
                       value="{{ request('max_balance') }}" step="0.01" placeholder="1000.00">
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="search">{{ __('messages.Search_Name') }}</label>
                <input type="text" name="search" id="search" class="form-control" 
                       value="{{ request('search') }}" placeholder="{{ __('messages.Manager_Name') }}">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> {{ __('messages.Filter') }}
                </button>
                <a href="{{ route('providers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ __('messages.Clear_Filters') }}
                </a>
            </div>
        </form>
    </div>
</div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.provider_List') }}</h6>
            <span class="badge badge-info">{{ count($providers) }} {{ __('messages.Total_Providers') }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Photo') }}</th>
                            <th>{{ __('messages.Name') }}</th>
                            <th>{{ __('messages.Phone') }}</th>
                            <th>{{ __('messages.Email') }}</th>
                            <th>{{ __('messages.Created_At') }}</th>
                            <th>{{ __('messages.Provider_Types') }}</th>
                            <th>{{ __('messages.Balance') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($providers as $provider)
                        <tr>
                            <td>{{ $provider->id }}</td>
                            <td>
                                @if($provider->photo_of_manager)
                                <img src="{{ asset('assets/admin/uploads/' . $provider->photo_of_manager) }}" alt="{{ $provider->name }}" width="50" class="rounded">
                                @else
                                <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="No Image" width="50" class="rounded">
                                @endif
                            </td>
                            <td>{{ $provider->name_of_manager }}</td>
                            <td>{{ $provider->country_code }} {{ $provider->phone }}</td>
                            <td>{{ $provider->email }}</td>
                            <td>{{ $provider->created_at }}</td>
                            <td>
                                @if($provider->providerTypes->count() > 0)
                                    @foreach($provider->providerTypes as $providerType)
                                        <span class="badge badge-secondary">{{ $providerType->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No types assigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $provider->balance > 0 ? 'badge-success' : ($provider->balance < 0 ? 'badge-danger' : 'badge-warning') }}">
                                    {{ number_format($provider->balance, 2) }} JD
                                </span>
                            </td>
                            <td>
                                @if($provider->activate == 1)
                                <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                @else
                                <span class="badge badge-danger">{{ __('messages.Inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.providerDetails.index', $provider->id) }}" class="btn btn-info btn-sm" title="{{ __('messages.View_Details') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('providers.show', $provider->id) }}" class="btn btn-info btn-sm" title="{{ __('messages.View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('providers.edit', $provider->id) }}" class="btn btn-primary btn-sm" title="{{ __('messages.Edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm wallet-btn" 
                                            onclick="openWalletModal('{{ $provider->id }}', '{{ addslashes($provider->name_of_manager) }}', '{{ $provider->balance }}')"
                                            title="Wallet Management">
                                        <i class="fas fa-wallet"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ __('messages.No_Providers_Found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Wallet Management Modal -->
<div class="modal fade" id="walletModal" tabindex="-1" role="dialog" aria-labelledby="walletModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="walletModalLabel">
                    <i class="fas fa-wallet"></i> Provider Wallet Management
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="walletForm" action="{{ route('provider.wallet.update') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="providerId" name="provider_id" value="">
                    
                    <!-- Provider Info Display -->
                    <div class="alert alert-info">
                        <strong>Provider Manager:</strong> <span id="providerNameDisplay"></span><br>
                        <strong>Current Balance:</strong> <span id="currentBalance"></span> JD
                    </div>
                    
                    <!-- Transaction Type -->
                    <div class="form-group">
                        <label for="transactionType">Transaction Type</label>
                        <select class="form-control" id="transactionType" name="type_of_transaction" required>
                            <option value="">Select Transaction Type</option>
                            <option value="1">Add to Wallet</option>
                            <option value="2">Deduct from Wallet</option>
                        </select>
                    </div>
                    
                    <!-- Amount -->
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">JD</span>
                            </div>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   placeholder="0.00" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    
                    <!-- Note -->
                    <div class="form-group">
                        <label for="note">Note (Optional)</label>
                        <textarea class="form-control" id="note" name="note" rows="3" 
                                  placeholder="Add a note for this transaction..."></textarea>
                    </div>
                    
                    <!-- Preview -->
                    <div id="transactionPreview" class="alert" style="display: none;">
                        <strong>Preview:</strong><br>
                        <span id="previewText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="fas fa-save"></i> Update Wallet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    // Function to open wallet modal with provider data
    function openWalletModal(providerId, providerName, providerBalance) {
        // Set the values in the modal
        $('#providerId').val(providerId);
        $('#providerNameDisplay').text(providerName);
        $('#currentBalance').text(parseFloat(providerBalance || 0).toFixed(2));
        
        // Reset form
        $('#walletForm')[0].reset();
        $('#providerId').val(providerId); // Set again after reset
        $('#transactionPreview').hide();
        $('#submitBtn').prop('disabled', true);
        
        // Show the modal
        $('#walletModal').modal('show');
        
        // Debug logs
        console.log('Opening wallet modal for:');
        console.log('Provider ID:', providerId);
        console.log('Provider Name:', providerName);
        console.log('Provider Balance:', providerBalance);
    }
    
    $(document).ready(function() {
        // Handle form changes for preview
        $('#transactionType, #amount').on('change input', function() {
            updatePreview();
        });
        
        function updatePreview() {
            var type = $('#transactionType').val();
            var amount = parseFloat($('#amount').val()) || 0;
            var currentBalance = parseFloat($('#currentBalance').text()) || 0;
            
            if (type && amount > 0) {
                var newBalance;
                var actionText;
                var alertClass;
                
                if (type == '1') { // Add
                    newBalance = currentBalance + amount;
                    actionText = 'ADD ' + amount.toFixed(2) + ' JD';
                    alertClass = 'alert-success';
                } else { // Deduct
                    newBalance = currentBalance - amount;
                    actionText = 'DEDUCT ' + amount.toFixed(2) + ' JD';
                    alertClass = 'alert-warning';
                    
                    if (newBalance < 0) {
                        alertClass = 'alert-danger';
                    }
                }
                
                $('#previewText').html(
                    actionText + '<br>' +
                    'New Balance: ' + newBalance.toFixed(2) + ' JD' +
                    (newBalance < 0 ? ' <strong>(NEGATIVE BALANCE)</strong>' : '')
                );
                
                $('#transactionPreview')
                    .removeClass('alert-success alert-warning alert-danger')
                    .addClass(alertClass)
                    .show();
                
                $('#submitBtn').prop('disabled', false);
            } else {
                $('#transactionPreview').hide();
                $('#submitBtn').prop('disabled', true);
            }
        }
        
        // Handle form submission
        $('#walletForm').on('submit', function(e) {
            var amount = parseFloat($('#amount').val());
            var type = $('#transactionType').val();
            var currentBalance = parseFloat($('#currentBalance').text());
            
            if (type == '2' && amount > currentBalance) {
                if (!confirm('This will result in a negative balance. Are you sure you want to continue?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
</script>
@endsection