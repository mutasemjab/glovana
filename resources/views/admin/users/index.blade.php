@extends('layouts.admin')

@section('title', __('messages.Users'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Users') }}</h1>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.Add_New_User') }}
        </a>
    </div>
    
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.User_List') }}</h6>
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
                            <th>{{ __('messages.Balance') }}</th>
                            <th>{{ __('messages.Points') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                @if($user->photo)
                                <img src="{{ asset('assets/admin/uploads/' . $user->photo) }}" alt="{{ $user->name }}" width="50">
                                @else
                                <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="No Image" width="50">
                                @endif
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->country_code }} {{ $user->phone }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ number_format($user->balance, 2) }} JD</td>
                            <td>
                                <span class="badge badge-info">{{ number_format($user->total_points) }} pts</span>
                            </td>
                            <td>
                                @if($user->activate == 1)
                                <span class="badge badge-success">{{ __('messages.Active') }}</span>
                                @else
                                <span class="badge badge-danger">{{ __('messages.Inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm wallet-btn" 
                                            onclick="openWalletModal('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->balance }}')">
                                        <i class="fas fa-wallet"></i>
                                    </button>
                                    <a href="{{ route('users.points.history', $user->id) }}" class="btn btn-warning btn-sm" title="{{ __('messages.Points_History') }}">
                                        <i class="fas fa-star"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination Links (if using pagination) -->
        @if(method_exists($users, 'links'))
        <div class="d-flex justify-content-center">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Wallet Management Modal -->
<div class="modal fade" id="walletModal" tabindex="-1" role="dialog" aria-labelledby="walletModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="walletModalLabel">
                    <i class="fas fa-wallet"></i> Wallet Management
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="walletForm" action="{{ route('wallet.update') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="userId" name="user_id" value="">
                    
                    <!-- User Info Display -->
                    <div class="alert alert-info">
                        <strong>User:</strong> <span id="userNameDisplay"></span><br>
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
    // Function to open wallet modal with user data
    function openWalletModal(userId, userName, userBalance) {
        // Set the values in the modal
        $('#userId').val(userId);
        $('#userNameDisplay').text(userName);
        $('#currentBalance').text(parseFloat(userBalance || 0).toFixed(2));
        
        // Reset form
        $('#walletForm')[0].reset();
        $('#userId').val(userId); // Set again after reset
        $('#transactionPreview').hide();
        $('#submitBtn').prop('disabled', true);
        
        // Show the modal
        $('#walletModal').modal('show');
        
        // Debug logs
        console.log('Opening wallet modal for:');
        console.log('User ID:', userId);
        console.log('User Name:', userName);
        console.log('User Balance:', userBalance);
    }
    
    $(document).ready(function() {
        // Handle wallet form changes for preview
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
        
        // Handle wallet form submission
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