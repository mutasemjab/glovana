<?php $__env->startSection('title', __('messages.providers')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(__('messages.providers')); ?></h1>
        <a href="<?php echo e(route('providers.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo e(__('messages.Add_New_provider')); ?>

        </a>
    </div>

    <!-- Filter Form -->
   <!-- Filter Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.Filters')); ?></h6>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('providers.index')); ?>" class="row">
            <div class="col-md-3 mb-3">
                <label for="status"><?php echo e(__('messages.Status')); ?></label>
                <select name="status" id="status" class="form-control">
                    <option value=""><?php echo e(__('messages.All_Status')); ?></option>
                    <option value="1" <?php echo e(request('status') == '1' ? 'selected' : ''); ?>><?php echo e(__('messages.Active')); ?></option>
                    <option value="0" <?php echo e(request('status') == '0' ? 'selected' : ''); ?>><?php echo e(__('messages.Inactive')); ?></option>
                </select>
            </div>
            
            <!-- NEW: Provider Type Filter -->
            <div class="col-md-3 mb-3">
                <label for="type_id"><?php echo e(__('messages.Provider_Type')); ?></label>
                <select name="type_id" id="type_id" class="form-control">
                    <option value=""><?php echo e(__('messages.All_Types')); ?></option>
                    <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type->id); ?>" <?php echo e(request('type_id') == $type->id ? 'selected' : ''); ?>>
                            <?php echo e($type->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="balance_type"><?php echo e(__('messages.Balance_Type')); ?></label>
                <select name="balance_type" id="balance_type" class="form-control">
                    <option value=""><?php echo e(__('messages.All_Balances')); ?></option>
                    <option value="positive" <?php echo e(request('balance_type') == 'positive' ? 'selected' : ''); ?>><?php echo e(__('messages.Positive_Balance')); ?></option>
                    <option value="negative" <?php echo e(request('balance_type') == 'negative' ? 'selected' : ''); ?>><?php echo e(__('messages.Negative_Balance')); ?></option>
                    <option value="zero" <?php echo e(request('balance_type') == 'zero' ? 'selected' : ''); ?>><?php echo e(__('messages.Zero_Balance')); ?></option>
                </select>
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="min_balance"><?php echo e(__('messages.Min_Balance')); ?></label>
                <input type="number" name="min_balance" id="min_balance" class="form-control" 
                       value="<?php echo e(request('min_balance')); ?>" step="0.01" placeholder="0.00">
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="max_balance"><?php echo e(__('messages.Max_Balance')); ?></label>
                <input type="number" name="max_balance" id="max_balance" class="form-control" 
                       value="<?php echo e(request('max_balance')); ?>" step="0.01" placeholder="1000.00">
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="search"><?php echo e(__('messages.Search_Name')); ?></label>
                <input type="text" name="search" id="search" class="form-control" 
                       value="<?php echo e(request('search')); ?>" placeholder="<?php echo e(__('messages.Manager_Name')); ?>">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> <?php echo e(__('messages.Filter')); ?>

                </button>
                <a href="<?php echo e(route('providers.index')); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> <?php echo e(__('messages.Clear_Filters')); ?>

                </a>
            </div>
        </form>
    </div>
</div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.provider_List')); ?></h6>
            <span class="badge badge-info"><?php echo e(count($providers)); ?> <?php echo e(__('messages.Total_Providers')); ?></span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?php echo e(__('messages.ID')); ?></th>
                            <th><?php echo e(__('messages.Photo')); ?></th>
                            <th><?php echo e(__('messages.Name')); ?></th>
                            <th><?php echo e(__('messages.Phone')); ?></th>
                            <th><?php echo e(__('messages.Email')); ?></th>
                            <th><?php echo e(__('messages.Created_At')); ?></th>
                            <th><?php echo e(__('messages.Provider_Types')); ?></th>
                            <th><?php echo e(__('messages.Balance')); ?></th>
                            <th><?php echo e(__('messages.Status')); ?></th>
                            <th><?php echo e(__('messages.Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($provider->id); ?></td>
                            <td>
                                <?php if($provider->photo_of_manager): ?>
                                <img src="<?php echo e(asset('assets/admin/uploads/' . $provider->photo_of_manager)); ?>" alt="<?php echo e($provider->name); ?>" width="50" class="rounded">
                                <?php else: ?>
                                <img src="<?php echo e(asset('assets/admin/img/no-image.png')); ?>" alt="No Image" width="50" class="rounded">
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($provider->name_of_manager); ?></td>
                            <td><?php echo e($provider->country_code); ?> <?php echo e($provider->phone); ?></td>
                            <td><?php echo e($provider->email); ?></td>
                            <td><?php echo e($provider->created_at); ?></td>
                            <td>
                                <?php if($provider->providerTypes->count() > 0): ?>
                                    <?php $__currentLoopData = $provider->providerTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $providerType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="badge badge-secondary"><?php echo e($providerType->name); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <span class="text-muted">No types assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo e($provider->balance > 0 ? 'badge-success' : ($provider->balance < 0 ? 'badge-danger' : 'badge-warning')); ?>">
                                    <?php echo e(number_format($provider->balance, 2)); ?> JD
                                </span>
                            </td>
                            <td>
                                <?php if($provider->activate == 1): ?>
                                <span class="badge badge-success"><?php echo e(__('messages.Active')); ?></span>
                                <?php else: ?>
                                <span class="badge badge-danger"><?php echo e(__('messages.Inactive')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo e(route('admin.providerDetails.index', $provider->id)); ?>" class="btn btn-info btn-sm" title="<?php echo e(__('messages.View_Details')); ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('providers.show', $provider->id)); ?>" class="btn btn-info btn-sm" title="<?php echo e(__('messages.View')); ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('providers.edit', $provider->id)); ?>" class="btn btn-primary btn-sm" title="<?php echo e(__('messages.Edit')); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm wallet-btn" 
                                            onclick="openWalletModal('<?php echo e($provider->id); ?>', '<?php echo e(addslashes($provider->name_of_manager)); ?>', '<?php echo e($provider->balance); ?>')"
                                            title="Wallet Management">
                                        <i class="fas fa-wallet"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center"><?php echo e(__('messages.No_Providers_Found')); ?></td>
                        </tr>
                        <?php endif; ?>
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
            <form id="walletForm" action="<?php echo e(route('provider.wallet.update')); ?>" method="POST">
                <?php echo csrf_field(); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/providers/index.blade.php ENDPATH**/ ?>