<?php $__env->startSection('title', __('messages.Coupons')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(__('messages.Coupons')); ?></h1>
        <a href="<?php echo e(route('coupons.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo e(__('messages.Add_New_Coupon')); ?>

        </a>
    </div>

  

    <!-- Coupons Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.Coupons_List')); ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
              
                        <tr>
                            <th><?php echo e(__('messages.ID')); ?></th>
                            <th><?php echo e(__('messages.Code')); ?></th>
                            <th><?php echo e(__('messages.amount')); ?></th>
                            <th><?php echo e(__('messages.minimum_total')); ?></th>
                            <th><?php echo e(__('messages.expired_at')); ?></th>
                            <th><?php echo e(__('messages.Type')); ?></th>
                            <th><?php echo e(__('messages.Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $coupons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coupon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($coupon->id); ?></td>
                            <td>
                                <span class="font-weight-bold"><?php echo e($coupon->code); ?></span>
                            </td>
                            <td><?php echo e($coupon->amount); ?></td>
                            <td><?php echo e($coupon->minimum_total); ?></td>
                            <td><?php echo e($coupon->expired_at); ?></td>
                            <td><?php echo e($coupon->type == 1 ? 'Product' : 'Provider'); ?></td>
                           
                            <td>
                                <div class="btn-group">
                                  
                                    <a href="<?php echo e(route('coupons.edit', $coupon->id)); ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                </div>
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
        $('#dataTable').DataTable();
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/coupons/index.blade.php ENDPATH**/ ?>