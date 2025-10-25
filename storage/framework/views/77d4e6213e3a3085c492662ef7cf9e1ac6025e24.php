

<?php $__env->startSection('title', __('messages.Points_History')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-star text-warning"></i> <?php echo e(__('messages.Points_History')); ?>

        </h1>
        <div>
            <a href="<?php echo e(route('users.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> <?php echo e(__('messages.Back_to_Users')); ?>

            </a>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPointsModal">
                <i class="fas fa-plus"></i> <?php echo e(__('messages.Add_Points')); ?>

            </button>
        </div>
    </div>

    <!-- User Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <?php if($user->photo): ?>
                                <img src="<?php echo e(asset('assets/admin/uploads/' . $user->photo)); ?>" 
                                     alt="<?php echo e($user->name); ?>" class="rounded-circle" width="80" height="80">
                            <?php else: ?>
                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px;">
                                    <i class="fas fa-user text-white fa-2x"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h4 class="mb-1"><?php echo e($user->name); ?></h4>
                            <p class="text-muted mb-1">
                                <i class="fas fa-envelope"></i> <?php echo e($user->email); ?>

                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-phone"></i> <?php echo e($user->country_code); ?> <?php echo e($user->phone); ?>

                            </p>
                        </div>
                        <div class="col-md-4">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <h3 class="mb-0"><?php echo e(number_format($user->total_points)); ?></h3>
                                            <small><?php echo e(__('messages.Total_Points')); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <h3 class="mb-0"><?php echo e(number_format($user->balance, 2)); ?></h3>
                                            <small><?php echo e(__('messages.Balance')); ?> (JD)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <?php echo e(__('messages.Total_Earned')); ?>

                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                +<?php echo e(number_format($totalEarned)); ?> <?php echo e(__('messages.pts')); ?>

                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plus-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                <?php echo e(__('messages.Total_Deducted')); ?>

                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                -<?php echo e(number_format($totalDeducted)); ?> <?php echo e(__('messages.pts')); ?>

                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-minus-circle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <?php echo e(__('messages.Total_Transactions')); ?>

                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo e(number_format($transactions->total())); ?>

                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?php echo e(__('messages.Current_Balance')); ?>

                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo e(number_format($user->total_points)); ?> <?php echo e(__('messages.pts')); ?>

                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.Transaction_History')); ?></h6>
            
            <!-- Filter Form -->
            <form method="GET" action="<?php echo e(route('users.points.history', $user->id)); ?>" class="form-inline">
                <select name="type" class="form-control form-control-sm mr-2">
                    <option value=""><?php echo e(__('messages.All_Types')); ?></option>
                    <option value="1" <?php echo e(request('type') == '1' ? 'selected' : ''); ?>><?php echo e(__('messages.Added')); ?></option>
                    <option value="2" <?php echo e(request('type') == '2' ? 'selected' : ''); ?>><?php echo e(__('messages.Deducted')); ?></option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-filter"></i> <?php echo e(__('messages.Filter')); ?>

                </button>
                <?php if(request()->hasAny(['type'])): ?>
                    <a href="<?php echo e(route('users.points.history', $user->id)); ?>" class="btn btn-sm btn-outline-secondary ml-1">
                        <i class="fas fa-times"></i> <?php echo e(__('messages.Clear')); ?>

                    </a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="card-body">
            <?php if($transactions->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><?php echo e(__('messages.ID')); ?></th>
                                <th><?php echo e(__('messages.Date')); ?></th>
                                <th><?php echo e(__('messages.Type')); ?></th>
                                <th><?php echo e(__('messages.Points')); ?></th>
                                <th><?php echo e(__('messages.Performed_By')); ?></th>
                                <th><?php echo e(__('messages.Note')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($transaction->id); ?></td>
                                <td>
                                    <div><?php echo e($transaction->created_at->format('M d, Y')); ?></div>
                                    <small class="text-muted"><?php echo e($transaction->created_at->format('H:i:s')); ?></small>
                                </td>
                                <td>
                                    <?php if($transaction->type_of_transaction == 1): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-plus"></i> <?php echo e(__('messages.Added')); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-minus"></i> <?php echo e(__('messages.Deducted')); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($transaction->type_of_transaction == 1): ?>
                                        <span class="text-success font-weight-bold">
                                            +<?php echo e(number_format(abs($transaction->points))); ?> <?php echo e(__('messages.pts')); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-warning font-weight-bold">
                                            -<?php echo e(number_format(abs($transaction->points))); ?> <?php echo e(__('messages.pts')); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($transaction->admin_id): ?>
                                        <div>
                                            <i class="fas fa-user-shield text-primary"></i>
                                            <small><?php echo e(__('messages.Admin')); ?>: <?php echo e($transaction->admin->name ?? 'N/A'); ?></small>
                                        </div>
                                    <?php elseif($transaction->provider_id): ?>
                                        <div>
                                            <i class="fas fa-store text-info"></i>
                                            <small><?php echo e(__('messages.Provider')); ?>: <?php echo e($transaction->provider->name ?? 'N/A'); ?></small>
                                        </div>
                                    <?php else: ?>
                                        <div>
                                            <i class="fas fa-cogs text-secondary"></i>
                                            <small><?php echo e(__('messages.System')); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($transaction->note): ?>
                                        <span class="text-muted"><?php echo e($transaction->note); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted"><?php echo e(__('messages.No_Note')); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    <?php echo e($transactions->appends(request()->query())->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted"><?php echo e(__('messages.No_Transactions_Found')); ?></h5>
                    <p class="text-muted"><?php echo e(__('messages.No_Point_Transactions_Message')); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Points Modal -->
<div class="modal fade" id="addPointsModal" tabindex="-1" role="dialog" aria-labelledby="addPointsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPointsModalLabel">
                    <i class="fas fa-star"></i> <?php echo e(__('messages.Manage_Points')); ?>

                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo e(route('points.update')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="<?php echo e($user->id); ?>">
                    
                    <!-- User Info Display -->
                    <div class="alert alert-info">
                        <strong><?php echo e(__('messages.User')); ?>:</strong> <?php echo e($user->name); ?><br>
                        <strong><?php echo e(__('messages.Current_Points')); ?>:</strong> <?php echo e(number_format($user->total_points)); ?> <?php echo e(__('messages.pts')); ?>

                    </div>
                    
                    <!-- Transaction Type -->
                    <div class="form-group">
                        <label for="transactionType"><?php echo e(__('messages.Transaction_Type')); ?></label>
                        <select class="form-control" id="transactionType" name="type_of_transaction" required>
                            <option value=""><?php echo e(__('messages.Select_Transaction_Type')); ?></option>
                            <option value="1"><?php echo e(__('messages.Add_Points')); ?></option>
                            <option value="2"><?php echo e(__('messages.Deduct_Points')); ?></option>
                        </select>
                    </div>
                    
                    <!-- Points Amount -->
                    <div class="form-group">
                        <label for="pointsAmount"><?php echo e(__('messages.Points_Amount')); ?></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="pointsAmount" name="points" 
                                   placeholder="0" min="1" required>
                            <div class="input-group-append">
                                <span class="input-group-text"><?php echo e(__('messages.pts')); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Note -->
                    <div class="form-group">
                        <label for="note"><?php echo e(__('messages.Note')); ?> (<?php echo e(__('messages.Optional')); ?>)</label>
                        <textarea class="form-control" id="note" name="note" rows="3" 
                                  placeholder="<?php echo e(__('messages.Add_Note_Placeholder')); ?>"></textarea>
                    </div>
                    
                    <!-- Preview -->
                    <div id="transactionPreview" class="alert" style="display: none;">
                        <strong><?php echo e(__('messages.Preview')); ?>:</strong><br>
                        <span id="previewText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('messages.Cancel')); ?></button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="fas fa-save"></i> <?php echo e(__('messages.Update_Points')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    $(document).ready(function() {
        // Handle form changes for preview
        $('#transactionType, #pointsAmount').on('change input', function() {
            updatePreview();
        });
        
        function updatePreview() {
            var type = $('#transactionType').val();
            var points = parseInt($('#pointsAmount').val()) || 0;
            var currentPoints = <?php echo e($user->total_points); ?>;
            
            if (type && points > 0) {
                var newPoints;
                var actionText;
                var alertClass;
                
                if (type == '1') { // Add
                    newPoints = currentPoints + points;
                    actionText = "<?php echo e(__('messages.ADD')); ?>" + ' ' + points + ' <?php echo e(__('messages.pts')); ?>';
                    alertClass = 'alert-success';
                } else { // Deduct
                    newPoints = currentPoints - points;
                    actionText = "<?php echo e(__('messages.DEDUCT')); ?>" + ' ' + points + ' <?php echo e(__('messages.pts')); ?>';
                    alertClass = 'alert-warning';
                    
                    if (newPoints < 0) {
                        alertClass = 'alert-danger';
                    }
                }
                
                $('#previewText').html(
                    actionText + '<br>' +
                    "<?php echo e(__('messages.New_Points_Total')); ?>" + ': ' + newPoints + ' <?php echo e(__('messages.pts')); ?>' +
                    (newPoints < 0 ? ' <strong>(<?php echo e(__('messages.NEGATIVE_POINTS')); ?>)</strong>' : '')
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
        $('form').on('submit', function(e) {
            var points = parseInt($('#pointsAmount').val());
            var type = $('#transactionType').val();
            var currentPoints = <?php echo e($user->total_points); ?>;
            
            if (type == '2' && points > currentPoints) {
                if (!confirm("<?php echo e(__('messages.Negative_Points_Confirmation')); ?>")) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/users/points_history.blade.php ENDPATH**/ ?>