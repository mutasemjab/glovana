

<?php $__env->startSection('title', __('messages.note_vouchers')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><?php echo e(__('messages.note_vouchers')); ?></h3>
                    <a href="<?php echo e(route('note-vouchers.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?php echo e(__('messages.add_new')); ?>

                    </a>
                </div>
                <div class="card-body">
                   

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('messages.number')); ?></th>
                                    <th><?php echo e(__('messages.type')); ?></th>
                                    <th><?php echo e(__('messages.date')); ?></th>
                                    <th><?php echo e(__('messages.warehouse')); ?></th>
                                    <th><?php echo e(__('messages.order')); ?></th>
                                    <th><?php echo e(__('messages.products_count')); ?></th>
                                    <th><?php echo e(__('messages.actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $noteVouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($voucher->number); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($voucher->type_class); ?>">
                                                <?php echo e($voucher->type_text); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($voucher->date_note_voucher->format('Y-m-d')); ?></td>
                                        <td><?php echo e($voucher->warehouse->name ?? '-'); ?></td>
                                        <td><?php echo e($voucher->order->id ?? '-'); ?></td>
                                        <td><?php echo e($voucher->voucher_products_count); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?php echo e(route('note-vouchers.show', $voucher)); ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo e(route('note-vouchers.edit', $voucher)); ?>" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="<?php echo e(route('note-vouchers.destroy', $voucher)); ?>" 
                                                      method="POST" 
                                                      style="display: inline-block;"
                                                      onsubmit="return confirm('<?php echo e(__('messages.confirm_delete')); ?>')">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center"><?php echo e(__('messages.no_data_available')); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php echo e($noteVouchers->links()); ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/note_vouchers/index.blade.php ENDPATH**/ ?>