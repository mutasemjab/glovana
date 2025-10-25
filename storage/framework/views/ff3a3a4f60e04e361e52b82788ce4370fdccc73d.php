<?php $__env->startSection('title', __('messages.edit_order')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?php echo e(__('messages.edit_order')); ?> #<?php echo e($order->number); ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('messages.dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('orders.index')); ?>"><?php echo e(__('messages.orders')); ?></a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('orders.show', $order->id)); ?>"><?php echo e(__('messages.order_details')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo e(__('messages.edit')); ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <form action="<?php echo e(route('orders.update', $order->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        
        <div class="row">
            <!-- Edit Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><?php echo e(__('messages.order_information')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo e(__('messages.order_status')); ?> <span class="text-danger">*</span></label>
                                    <select name="order_status" class="form-control <?php $__errorArgs = ['order_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                        <option value=""><?php echo e(__('messages.select_status')); ?></option>
                                        <option value="1" <?php echo e($order->order_status == 1 ? 'selected' : ''); ?>><?php echo e(__('messages.pending')); ?></option>
                                        <option value="2" <?php echo e($order->order_status == 2 ? 'selected' : ''); ?>><?php echo e(__('messages.accepted')); ?></option>
                                        <option value="3" <?php echo e($order->order_status == 3 ? 'selected' : ''); ?>><?php echo e(__('messages.on_the_way')); ?></option>
                                        <option value="4" <?php echo e($order->order_status == 4 ? 'selected' : ''); ?>><?php echo e(__('messages.delivered')); ?></option>
                                        <option value="5" <?php echo e($order->order_status == 5 ? 'selected' : ''); ?>><?php echo e(__('messages.canceled')); ?></option>
                                        <option value="6" <?php echo e($order->order_status == 6 ? 'selected' : ''); ?>><?php echo e(__('messages.refund')); ?></option>
                                    </select>
                                    <?php $__errorArgs = ['order_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo e(__('messages.payment_status')); ?> <span class="text-danger">*</span></label>
                                    <select name="payment_status" class="form-control <?php $__errorArgs = ['payment_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                        <option value=""><?php echo e(__('messages.select_status')); ?></option>
                                        <option value="1" <?php echo e($order->payment_status == 1 ? 'selected' : ''); ?>><?php echo e(__('messages.paid')); ?></option>
                                        <option value="2" <?php echo e($order->payment_status == 2 ? 'selected' : ''); ?>><?php echo e(__('messages.unpaid')); ?></option>
                                    </select>
                                    <?php $__errorArgs = ['payment_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo e(__('messages.admin_note')); ?></label>
                                    <textarea name="note" class="form-control <?php $__errorArgs = ['note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="4" placeholder="<?php echo e(__('messages.add_admin_note')); ?>"><?php echo e(old('note', $order->note)); ?></textarea>
                                    <?php $__errorArgs = ['note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <div class="form-text"><?php echo e(__('messages.admin_note_help')); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i>
                                    <strong><?php echo e(__('messages.important_notes')); ?>:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><?php echo e(__('messages.status_change_note')); ?></li>
                                        <li><?php echo e(__('messages.payment_status_note')); ?></li>
                                        <li><?php echo e(__('messages.refund_note')); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo e(route('orders.show', $order->id)); ?>" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> <?php echo e(__('messages.cancel')); ?>

                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> <?php echo e(__('messages.update_order')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary (Read Only) -->
            <div class="col-lg-4">
                <!-- Current Status -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><?php echo e(__('messages.current_status')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?php echo e(__('messages.order_number')); ?></label>
                            <p class="form-control-static"><strong>#<?php echo e($order->number); ?></strong></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo e(__('messages.current_order_status')); ?></label>
                            <p class="form-control-static">
                                <span class="badge bg-<?php echo e($order->status_color); ?> fs-6">
                                    <?php echo e($order->order_status_label); ?>

                                </span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo e(__('messages.current_payment_status')); ?></label>
                            <p class="form-control-static">
                                <span class="badge <?php echo e($order->payment_status == 1 ? 'bg-success' : 'bg-warning'); ?> fs-6">
                                    <?php echo e($order->payment_status_label); ?>

                                </span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo e(__('messages.payment_type')); ?></label>
                            <p class="form-control-static">
                                <span class="badge bg-info fs-6"><?php echo e(ucfirst($order->payment_type)); ?></span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><?php echo e(__('messages.customer_information')); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if($order->user): ?>
                            <div class="mb-3">
                                <label class="form-label"><?php echo e(__('messages.customer_name')); ?></label>
                                <p class="form-control-static"><?php echo e($order->user->name); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo e(__('messages.phone_number')); ?></label>
                                <p class="form-control-static"><?php echo e($order->user->country_code); ?><?php echo e($order->user->phone); ?></p>
                            </div>
                            <?php if($order->user->email): ?>
                            <div class="mb-3">
                                <label class="form-label"><?php echo e(__('messages.email')); ?></label>
                                <p class="form-control-static"><?php echo e($order->user->email); ?></p>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted"><?php echo e(__('messages.no_customer_data')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><?php echo e(__('messages.order_summary')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm">
                                <tbody>
                                    <tr>
                                        <td><?php echo e(__('messages.total_items')); ?>:</td>
                                        <td class="text-end"><?php echo e($order->total_items); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo e(__('messages.delivery_fee')); ?>:</td>
                                        <td class="text-end"><?php echo e(number_format($order->delivery_fee, 2)); ?> <?php echo e(__('messages.jd')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo e(__('messages.total_taxes')); ?>:</td>
                                        <td class="text-end"><?php echo e(number_format($order->total_taxes, 2)); ?> <?php echo e(__('messages.jd')); ?></td>
                                    </tr>
                                    <?php if($order->total_discounts > 0): ?>
                                    <tr>
                                        <td><?php echo e(__('messages.total_discounts')); ?>:</td>
                                        <td class="text-end text-success">-<?php echo e(number_format($order->total_discounts, 2)); ?> <?php echo e(__('messages.jd')); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if($order->coupon_discount > 0): ?>
                                    <tr>
                                        <td><?php echo e(__('messages.coupon_discount')); ?>:</td>
                                        <td class="text-end text-success">-<?php echo e(number_format($order->coupon_discount, 2)); ?> <?php echo e(__('messages.jd')); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr class="table-active">
                                        <td><strong><?php echo e(__('messages.total_amount')); ?>:</strong></td>
                                        <td class="text-end"><strong><?php echo e(number_format($order->total_prices, 2)); ?> <?php echo e(__('messages.jd')); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show confirmation for status changes
    const orderStatusSelect = document.querySelector('select[name="order_status"]');
    const paymentStatusSelect = document.querySelector('select[name="payment_status"]');
    
    if (orderStatusSelect) {
        orderStatusSelect.addEventListener('change', function() {
            if (this.value == '6') { // Refund
                if (!confirm('<?php echo e(__("messages.refund_confirmation")); ?>')) {
                    this.value = '<?php echo e($order->order_status); ?>';
                }
            } else if (this.value == '5') { // Canceled
                if (!confirm('<?php echo e(__("messages.cancel_confirmation")); ?>')) {
                    this.value = '<?php echo e($order->order_status); ?>';
                }
            }
        });
    }
    
    if (paymentStatusSelect) {
        paymentStatusSelect.addEventListener('change', function() {
            if (this.value == '1' && '<?php echo e($order->payment_type); ?>' === 'wallet') {
                if (!confirm('<?php echo e(__("messages.wallet_payment_confirmation")); ?>')) {
                    this.value = '<?php echo e($order->payment_status); ?>';
                }
            }
        });
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/orders/edit.blade.php ENDPATH**/ ?>