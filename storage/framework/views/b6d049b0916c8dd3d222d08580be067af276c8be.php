<?php $__env->startSection('title', __('messages.Wallet_Transactions')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(__('messages.Wallet_Transactions')); ?></h1>
        <a href="<?php echo e(route('wallet_transactions.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo e(__('messages.Add_New_Transaction')); ?>

        </a>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo e(session('success')); ?>

        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo e(session('error')); ?>

        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endif; ?>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.Filter_Transactions')); ?></h6>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('wallet_transactions.filter')); ?>" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="entity_type"><?php echo e(__('messages.Entity_Type')); ?></label>
                            <select class="form-control" id="entity_type" name="entity_type">
                                <option value="all" <?php echo e(request('entity_type') == 'all' ? 'selected' : ''); ?>><?php echo e(__('messages.All')); ?></option>
                                <option value="user" <?php echo e(request('entity_type') == 'user' ? 'selected' : ''); ?>><?php echo e(__('messages.Users')); ?></option>
                                <option value="provider" <?php echo e(request('entity_type') == 'provider' ? 'selected' : ''); ?>><?php echo e(__('messages.providers')); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3 entity-select user-select" style="display: <?php echo e(request('entity_type') == 'user' ? 'block' : 'none'); ?>;">
                        <div class="form-group">
                            <label for="user_id"><?php echo e(__('messages.Select_User')); ?></label>
                            <select class="form-control" id="user_id" name="entity_id">
                                <option value=""><?php echo e(__('messages.All_Users')); ?></option>
                                <?php $__currentLoopData = $users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>" <?php echo e(request('entity_id') == $user->id && request('entity_type') == 'user' ? 'selected' : ''); ?>>
                                    <?php echo e($user->name); ?> (<?php echo e($user->phone); ?>)
                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3 entity-select driver-select" style="display: <?php echo e(request('entity_type') == 'driver' ? 'block' : 'none'); ?>;">
                        <div class="form-group">
                            <label for="driver_id"><?php echo e(__('messages.Select_Driver')); ?></label>
                            <select class="form-control" id="driver_id" name="entity_id">
                                <option value=""><?php echo e(__('messages.All_Drivers')); ?></option>
                                <?php $__currentLoopData = $drivers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($driver->id); ?>" <?php echo e(request('entity_id') == $driver->id && request('entity_type') == 'driver' ? 'selected' : ''); ?>>
                                    <?php echo e($driver->name); ?> (<?php echo e($driver->phone); ?>)
                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="transaction_type"><?php echo e(__('messages.Transaction_Type')); ?></label>
                            <select class="form-control" id="transaction_type" name="transaction_type">
                                <option value="all" <?php echo e(request('transaction_type') == 'all' ? 'selected' : ''); ?>><?php echo e(__('messages.All_Types')); ?></option>
                                <option value="1" <?php echo e(request('transaction_type') == '1' ? 'selected' : ''); ?>><?php echo e(__('messages.Deposit')); ?></option>
                                <option value="2" <?php echo e(request('transaction_type') == '2' ? 'selected' : ''); ?>><?php echo e(__('messages.Withdrawal')); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from"><?php echo e(__('messages.Date_From')); ?></label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo e(request('date_from')); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to"><?php echo e(__('messages.Date_To')); ?></label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo e(request('date_to')); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> <?php echo e(__('messages.Filter')); ?>

                        </button>
                        <a href="<?php echo e(route('wallet_transactions.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> <?php echo e(__('messages.Reset')); ?>

                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.Wallet_Transactions_List')); ?></h6>
            <div>
                <span class="badge badge-success px-3 py-2 mr-2">
                    <?php echo e(__('messages.Total_Deposits')); ?>: 
                    <?php echo e($transactions->where('type_of_transaction', 1)->sum('amount')); ?>

                </span>
                <span class="badge badge-danger px-3 py-2">
                    <?php echo e(__('messages.Total_Withdrawals')); ?>: 
                    <?php echo e($transactions->where('type_of_transaction', 2)->sum('amount')); ?>

                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?php echo e(__('messages.ID')); ?></th>
                            <th><?php echo e(__('messages.Date')); ?></th>
                            <th><?php echo e(__('messages.Entity')); ?></th>
                            <th><?php echo e(__('messages.Amount')); ?></th>
                            <th><?php echo e(__('messages.Type')); ?></th>
                            <th><?php echo e(__('messages.Note')); ?></th>
                            <th><?php echo e(__('messages.Created_By')); ?></th>
                            <th><?php echo e(__('messages.Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($transaction->id); ?></td>
                            <td><?php echo e($transaction->created_at->format('Y-m-d H:i')); ?></td>
                            <td>
                                <?php if($transaction->user_id): ?>
                                    <span class="badge badge-info"><?php echo e(__('messages.User')); ?></span>
                                    <a href="<?php echo e(route('users.show', $transaction->user_id)); ?>">
                                        <?php echo e($transaction->user->name ?? 'N/A'); ?>

                                    </a>
                                <?php elseif($transaction->driver_id): ?>
                                    <span class="badge badge-primary"><?php echo e(__('messages.Driver')); ?></span>
                                    <a href="<?php echo e(route('drivers.show', $transaction->driver_id)); ?>">
                                        <?php echo e($transaction->driver->name ?? 'N/A'); ?>

                                    </a>
                                <?php else: ?>
                                    <?php echo e(__('messages.Unknown')); ?>

                                <?php endif; ?>
                            </td>
                            <td class="<?php echo e($transaction->type_of_transaction == 1 ? 'text-success' : 'text-danger'); ?> font-weight-bold">
                                <?php echo e($transaction->getFormattedAmount()); ?>

                            </td>
                            <td>
                                <span class="badge badge-<?php echo e($transaction->type_of_transaction == 1 ? 'success' : 'danger'); ?>">
                                    <?php echo e($transaction->getTransactionTypeText()); ?>

                                </span>
                            </td>
                            <td>
                                <small><?php echo e($transaction->note ?? __('messages.No_Note')); ?></small>
                            </td>
                            <td>
                                <?php echo e($transaction->admin->name ?? __('messages.System')); ?>

                            </td>
                            <td>
                                <a href="<?php echo e(route('wallet_transactions.show', $transaction->id)); ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "order": [[1, "desc"]]
        });
        
        // Handle entity type selection
        $('#entity_type').on('change', function() {
            $('.entity-select').hide();
            
            if ($(this).val() == 'user') {
                $('.user-select').show();
                $('#driver_id').val('');
            } else if ($(this).val() == 'driver') {
                $('.driver-select').show();
                $('#user_id').val('');
            } else {
                // All selected, clear both
                $('#user_id, #driver_id').val('');
            }
        });
        
        // Date validation
        $('#date_to').on('change', function() {
            var startDate = $('#date_from').val();
            var endDate = $(this).val();
            
            if (startDate && endDate && startDate > endDate) {
                alert("<?php echo e(__('messages.Date_Range_Error')); ?>");
                $(this).val('');
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/wallet_transactions/index.blade.php ENDPATH**/ ?>