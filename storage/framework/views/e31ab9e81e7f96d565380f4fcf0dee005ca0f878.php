<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4><?php echo e(__('messages.Deliveries')); ?></h4>
                    <a href="<?php echo e(route('deliveries.create')); ?>" class="btn btn-primary">
                        <?php echo e(__('messages.Add_Delivery')); ?>

                    </a>
                </div>
                <div class="card-body">
                  
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('messages.ID')); ?></th>
                                    <th><?php echo e(__('messages.Place')); ?></th>
                                    <th><?php echo e(__('messages.Price')); ?></th>
                                    <th><?php echo e(__('messages.Created_At')); ?></th>
                                    <th><?php echo e(__('messages.Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $deliveries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $delivery): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($delivery->id); ?></td>
                                        <td><?php echo e($delivery->place); ?></td>
                                        <td><?php echo e(number_format($delivery->price, 2)); ?> <?php echo e(__('messages.Currency')); ?></td>
                                        <td><?php echo e(\Carbon\Carbon::parse($delivery->created_at)->format('Y-m-d H:i')); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('deliveries.edit', $delivery->id)); ?>" 
                                               class="btn btn-sm btn-warning">
                                                <?php echo e(__('messages.Edit')); ?>

                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <?php echo e(__('messages.No_Deliveries_Found')); ?>

                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/deliveries/index.blade.php ENDPATH**/ ?>