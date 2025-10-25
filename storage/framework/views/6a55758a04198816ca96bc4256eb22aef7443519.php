

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4><?php echo e(__('messages.coupons_and_users')); ?></h4>
                </div>
                <div class="card-body">
                    <?php if($coupons->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th><?php echo e(__('messages.id')); ?></th>
                                        <th><?php echo e(__('messages.code')); ?></th>
                                        <th><?php echo e(__('messages.amount')); ?></th>
                                        <th><?php echo e(__('messages.minimum_total')); ?></th>
                                        <th><?php echo e(__('messages.type')); ?></th>
                                        <th><?php echo e(__('messages.expired_at')); ?></th>
                                        <th><?php echo e(__('messages.users_count')); ?></th>
                                        <th><?php echo e(__('messages.users')); ?></th>
                                        <th><?php echo e(__('messages.created_at')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $coupons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coupon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($coupon->id); ?></td>
                                            <td>
                                                <span class="badge badge-primary"><?php echo e($coupon->code); ?></span>
                                            </td>
                                            <td>JD <?php echo e(number_format($coupon->amount, 2)); ?></td>
                                            <td>JD <?php echo e(number_format($coupon->minimum_total, 2)); ?></td>
                                            <td>
                                                <?php if($coupon->type == 1): ?>
                                                    <span class="badge badge-success"><?php echo e(__('messages.products')); ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-info"><?php echo e(__('messages.provider')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="<?php if(\Carbon\Carbon::parse($coupon->expired_at)->isPast()): ?> text-danger <?php else: ?> text-success <?php endif; ?>">
                                                    <?php echo e(\Carbon\Carbon::parse($coupon->expired_at)->format('M d, Y')); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary"><?php echo e($coupon->users->count()); ?></span>
                                            </td>
                                            <td>
                                                <?php if($coupon->users->count() > 0): ?>
                                                    <div class="users-list">
                                                        <?php $__currentLoopData = $coupon->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <div class="user-item mb-1">
                                                                <small class="badge badge-light">
                                                                    <?php echo e($user->name); ?> (<?php echo e($user->email); ?>)
                                                                </small>
                                                                <br>
                                                                <small class="text-muted">
                                                                    <?php echo e(__('messages.used')); ?>: <?php echo e(\Carbon\Carbon::parse($user->pivot->created_at)->format('M d, Y H:i')); ?>

                                                                </small>
                                                            </div>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted"><?php echo e(__('messages.no_users_yet')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($coupon->created_at->format('M d, Y')); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            <?php echo e($coupons->links()); ?>

                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <h5><?php echo e(__('messages.no_coupons_found')); ?></h5>
                            <p><?php echo e(__('messages.no_coupons_in_system')); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.users-list {
    max-height: 200px;
    overflow-y: auto;
}
.user-item {
    padding: 2px 0;
    border-bottom: 1px solid #f0f0f0;
}
.user-item:last-child {
    border-bottom: none;
}
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/coupons/used.blade.php ENDPATH**/ ?>