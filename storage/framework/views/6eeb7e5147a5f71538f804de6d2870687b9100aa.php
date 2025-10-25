<?php $__env->startSection('title', __('messages.Create_Transaction')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(__('messages.Create_Transaction')); ?></h1>
        <a href="<?php echo e(route('wallet_transactions.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?php echo e(__('messages.Back_to_List')); ?>

        </a>
    </div>

    <?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo e(session('error')); ?>

        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.Transaction_Details')); ?></h6>
        </div>
        <div class="card-body">
            <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <?php endif; ?>

            <form action="<?php echo e(route('wallet_transactions.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Entity Selection -->
                        <div class="form-group">
                            <label for="entity_type"><?php echo e(__('messages.Entity_Type')); ?> <span class="text-danger">*</span></label>
                            <select class="form-control" id="entity_type" name="entity_type" required>
                                <option value=""><?php echo e(__('messages.Select_Entity_Type')); ?></option>
                                <option value="user" <?php echo e(old('entity_type') == 'user' ? 'selected' : ''); ?>><?php echo e(__('messages.User')); ?></option>
                                <option value="provider" <?php echo e(old('entity_type') == 'provider' ? 'selected' : ''); ?>><?php echo e(__('messages.provider')); ?></option>
                            </select>
                        </div>
                        
                        <!-- User Selection -->
                        <div class="form-group entity-select user-select" style="display: <?php echo e(old('entity_type') == 'user' ? 'block' : 'none'); ?>;">
                            <label for="user_entity_id"><?php echo e(__('messages.Select_User')); ?> <span class="text-danger">*</span></label>
                            <select class="form-control" id="user_entity_id" name="entity_id">
                                <option value=""><?php echo e(__('messages.Select_User')); ?></option>
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>" <?php echo e(old('entity_id') == $user->id && old('entity_type') == 'user' ? 'selected' : ''); ?> data-balance="<?php echo e($user->balance); ?>">
                                    <?php echo e($user->name); ?> (<?php echo e($user->phone); ?>) - <?php echo e(__('messages.Balance')); ?>: <?php echo e($user->balance); ?>

                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        
                        <!-- provider Selection -->
                        <div class="form-group entity-select provider-select" style="display: <?php echo e(old('entity_type') == 'provider' ? 'block' : 'none'); ?>;">
                            <label for="provider_entity_id"><?php echo e(__('messages.Select_provider')); ?> <span class="text-danger">*</span></label>
                            <select class="form-control" id="provider_entity_id" name="entity_id">
                                <option value=""><?php echo e(__('messages.Select_provider')); ?></option>
                                <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($provider->id); ?>" <?php echo e(old('entity_id') == $provider->id && old('entity_type') == 'provider' ? 'selected' : ''); ?> data-balance="<?php echo e($provider->balance); ?>">
                                    <?php echo e($provider->providerTypes->first()->name); ?>  (<?php echo e($provider->phone); ?>) - <?php echo e(__('messages.Balance')); ?>: <?php echo e($provider->balance); ?>

                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        
                        <div class="alert alert-info current-balance mt-3" style="display: none;">
                            <strong><?php echo e(__('messages.Current_Balance')); ?>:</strong> <span id="balance-amount">0</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Transaction Details -->
                        <div class="form-group">
                            <label for="type_of_transaction"><?php echo e(__('messages.Transaction_Type')); ?> <span class="text-danger">*</span></label>
                            <select class="form-control" id="type_of_transaction" name="type_of_transaction" required>
                                <option value="1" <?php echo e(old('type_of_transaction', 1) == 1 ? 'selected' : ''); ?>><?php echo e(__('messages.Deposit')); ?></option>
                                <option value="2" <?php echo e(old('type_of_transaction') == 2 ? 'selected' : ''); ?>><?php echo e(__('messages.Withdrawal')); ?></option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount"><?php echo e(__('messages.Amount')); ?> <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="<?php echo e(old('amount')); ?>" required min="0.01">
                            <div class="text-danger mt-1" id="balance-warning" style="display: none;">
                                <?php echo e(__('messages.Insufficient_Balance_Warning')); ?>

                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="note"><?php echo e(__('messages.Note')); ?></label>
                            <textarea class="form-control" id="note" name="note" rows="3"><?php echo e(old('note')); ?></textarea>
                            <small class="form-text text-muted"><?php echo e(__('messages.Transaction_Note_Info')); ?></small>
                        </div>
                    </div>
                </div>

                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-primary submit-btn">
                        <i class="fas fa-save"></i> <?php echo e(__('messages.Save_Transaction')); ?>

                    </button>
                    <a href="<?php echo e(route('wallet_transactions.index')); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> <?php echo e(__('messages.Cancel')); ?>

                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/wallet_transactions/create.blade.php ENDPATH**/ ?>