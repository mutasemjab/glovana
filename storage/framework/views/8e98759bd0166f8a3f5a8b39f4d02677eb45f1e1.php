<?php $__env->startSection('title', __('messages.order_details')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?php echo e(__('messages.order_details')); ?> #<?php echo e($order->number); ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('messages.dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('orders.index')); ?>"><?php echo e(__('messages.orders')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo e(__('messages.order_details')); ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Information -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?php echo e(__('messages.order_information')); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><?php echo e(__('messages.order_number')); ?></label>
                                <p class="form-control-static"><strong>#<?php echo e($order->number); ?></strong></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><?php echo e(__('messages.order_date')); ?></label>
                                <p class="form-control-static"><?php echo e($order->date->format('Y-m-d H:i:s')); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><?php echo e(__('messages.order_status')); ?></label>
                                <p class="form-control-static">
                                    <span class="badge bg-<?php echo e($order->status_color); ?> fs-6">
                                        <?php echo e($order->order_status_label); ?>

                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><?php echo e(__('messages.payment_status')); ?></label>
                                <p class="form-control-static">
                                    <span class="badge <?php echo e($order->payment_status == 1 ? 'bg-success' : 'bg-warning'); ?> fs-6">
                                        <?php echo e($order->payment_status_label); ?>

                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><?php echo e(__('messages.payment_type')); ?></label>
                                <p class="form-control-static">
                                    <span class="badge bg-info fs-6"><?php echo e(ucfirst($order->payment_type)); ?></span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><?php echo e(__('messages.total_items')); ?></label>
                                <p class="form-control-static"><?php echo e($order->total_items); ?> <?php echo e(__('messages.items')); ?></p>
                            </div>
                        </div>
                        <?php if($order->note): ?>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label"><?php echo e(__('messages.note')); ?></label>
                                <p class="form-control-static"><?php echo e($order->note); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Order Products -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?php echo e(__('messages.order_products')); ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo e(__('messages.product')); ?></th>
                                    <th><?php echo e(__('messages.unit_price')); ?></th>
                                    <th><?php echo e(__('messages.quantity')); ?></th>
                                    <th><?php echo e(__('messages.tax_percentage')); ?></th>
                                    <th><?php echo e(__('messages.discount')); ?></th>
                                    <th><?php echo e(__('messages.total_price')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $order->orderProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $orderProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if($orderProduct->product->image): ?>
                                                <img src="<?php echo e(asset('storage/' . $orderProduct->product->image)); ?>" 
                                                     alt="<?php echo e($orderProduct->product->name); ?>" 
                                                     class="rounded" 
                                                     width="50" height="50">
                                            <?php endif; ?>
                                            <div class="ms-3">
                                                <h6 class="mb-1"><?php echo e($orderProduct->product->name); ?></h6>
                                                <?php if($orderProduct->product->description): ?>
                                                    <small class="text-muted"><?php echo e(Str::limit($orderProduct->product->description, 50)); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo e(number_format($orderProduct->unit_price, 2)); ?> <?php echo e(__('messages.jd')); ?></td>
                                    <td><?php echo e($orderProduct->quantity); ?></td>
                                    <td><?php echo e($orderProduct->tax_percentage); ?>%</td>
                                    <td>
                                        <?php if($orderProduct->discount_percentage): ?>
                                            <?php echo e($orderProduct->discount_percentage); ?>%
                                            (<?php echo e(number_format($orderProduct->discount_value, 2)); ?> <?php echo e(__('messages.jd')); ?>)
                                        <?php else: ?>
                                            <?php echo e(__('messages.no_discount')); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo e(number_format($orderProduct->total_price_after_tax, 2)); ?> <?php echo e(__('messages.jd')); ?></strong></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Summary Information -->
        <div class="col-lg-4">
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

            <!-- Delivery Address -->
            <?php if($order->address): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?php echo e(__('messages.delivery_address')); ?></h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('messages.address')); ?></label>
                        <p class="form-control-static"><?php echo e($order->address->address); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('messages.city')); ?></label>
                        <p class="form-control-static"><?php echo e($order->address->city); ?></p>
                    </div>
                    <?php if($order->address->state): ?>
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('messages.state')); ?></label>
                        <p class="form-control-static"><?php echo e($order->address->state); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?php echo e(__('messages.order_summary')); ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td><?php echo e(__('messages.subtotal')); ?>:</td>
                                    <td class="text-end">
                                        <?php echo e(number_format($order->total_prices - $order->delivery_fee - $order->total_taxes + $order->total_discounts, 2)); ?> 
                                        <?php echo e(__('messages.jd')); ?>

                                    </td>
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

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?php echo e(__('messages.actions')); ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('orders.edit', $order->id)); ?>" class="btn btn-primary">
                            <i class="ri-edit-line"></i> <?php echo e(__('messages.edit_order')); ?>

                        </a>
                        <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> <?php echo e(__('messages.back_to_orders')); ?>

                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/orders/show.blade.php ENDPATH**/ ?>