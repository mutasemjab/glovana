<?php $__env->startSection('title', __('messages.orders')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?php echo e(__('messages.orders')); ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('messages.dashboard')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo e(__('messages.orders')); ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate"><?php echo e(__('messages.total_orders')); ?></span>
                            <h4 class="mb-3"><?php echo e(number_format($statistics['total_orders'])); ?></h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title bg-primary rounded-circle">
                                    <i class="ri-shopping-cart-line font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate"><?php echo e(__('messages.pending_orders')); ?></span>
                            <h4 class="mb-3"><?php echo e(number_format($statistics['pending_orders'])); ?></h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-warning">
                                <span class="avatar-title bg-warning rounded-circle">
                                    <i class="ri-time-line font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate"><?php echo e(__('messages.delivered_orders')); ?></span>
                            <h4 class="mb-3"><?php echo e(number_format($statistics['delivered_orders'])); ?></h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-success">
                                <span class="avatar-title bg-success rounded-circle">
                                    <i class="ri-check-line font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate"><?php echo e(__('messages.total_revenue')); ?></span>
                            <h4 class="mb-3"><?php echo e(number_format($statistics['total_revenue'], 2)); ?> <?php echo e(__('messages.jd')); ?></h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-info">
                                <span class="avatar-title bg-info rounded-circle">
                                    <i class="ri-money-dollar-box-line font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><?php echo e(__('messages.filters')); ?></h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('orders.index')); ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label"><?php echo e(__('messages.order_status')); ?></label>
                                <select name="order_status" class="form-control">
                                    <option value=""><?php echo e(__('messages.all_statuses')); ?></option>
                                    <option value="1" <?php echo e(request('order_status') == '1' ? 'selected' : ''); ?>><?php echo e(__('messages.pending')); ?></option>
                                    <option value="2" <?php echo e(request('order_status') == '2' ? 'selected' : ''); ?>><?php echo e(__('messages.accepted')); ?></option>
                                    <option value="3" <?php echo e(request('order_status') == '3' ? 'selected' : ''); ?>><?php echo e(__('messages.on_the_way')); ?></option>
                                    <option value="4" <?php echo e(request('order_status') == '4' ? 'selected' : ''); ?>><?php echo e(__('messages.delivered')); ?></option>
                                    <option value="5" <?php echo e(request('order_status') == '5' ? 'selected' : ''); ?>><?php echo e(__('messages.canceled')); ?></option>
                                    <option value="6" <?php echo e(request('order_status') == '6' ? 'selected' : ''); ?>><?php echo e(__('messages.refund')); ?></option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><?php echo e(__('messages.payment_status')); ?></label>
                                <select name="payment_status" class="form-control">
                                    <option value=""><?php echo e(__('messages.all_statuses')); ?></option>
                                    <option value="1" <?php echo e(request('payment_status') == '1' ? 'selected' : ''); ?>><?php echo e(__('messages.paid')); ?></option>
                                    <option value="2" <?php echo e(request('payment_status') == '2' ? 'selected' : ''); ?>><?php echo e(__('messages.unpaid')); ?></option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label"><?php echo e(__('messages.from_date')); ?></label>
                                <input type="date" name="from_date" class="form-control" value="<?php echo e(request('from_date')); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label"><?php echo e(__('messages.to_date')); ?></label>
                                <input type="date" name="to_date" class="form-control" value="<?php echo e(request('to_date')); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label"><?php echo e(__('messages.search')); ?></label>
                                <input type="text" name="search" class="form-control" placeholder="<?php echo e(__('messages.search_orders')); ?>" value="<?php echo e(request('search')); ?>">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-search-line"></i> <?php echo e(__('messages.filter')); ?>

                                </button>
                                <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-secondary">
                                    <i class="ri-refresh-line"></i> <?php echo e(__('messages.reset')); ?>

                                </a>
                              
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><?php echo e(__('messages.orders_list')); ?></h4>
                </div>
                <div class="card-body">
                    <?php if($orders->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th><?php echo e(__('messages.order_number')); ?></th>
                                        <th><?php echo e(__('messages.customer')); ?></th>
                                        <th><?php echo e(__('messages.items_count')); ?></th>
                                        <th><?php echo e(__('messages.total_amount')); ?></th>
                                        <th><?php echo e(__('messages.order_status')); ?></th>
                                        <th><?php echo e(__('messages.payment_status')); ?></th>
                                        <th><?php echo e(__('messages.payment_type')); ?></th>
                                        <th><?php echo e(__('messages.date')); ?></th>
                                        <th><?php echo e(__('messages.actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><strong>#<?php echo e($order->number); ?></strong></td>
                                            <td>
                                                <div><?php echo e($order->user->name ?? __('messages.no_customer')); ?></div>
                                                <small class="text-muted"><?php echo e($order->user->phone ?? ''); ?></small>
                                            </td>
                                            <td><?php echo e($order->items_count); ?></td>
                                            <td><?php echo e(number_format($order->total_prices, 2)); ?> <?php echo e(__('messages.jd')); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo e($order->status_color); ?>">
                                                    <?php echo e($order->order_status_label); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo e($order->payment_status == 1 ? 'bg-success' : 'bg-warning'); ?>">
                                                    <?php echo e($order->payment_status_label); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo e(ucfirst($order->payment_type)); ?></span>
                                            </td>
                                            <td><?php echo e($order->date->format('Y-m-d H:i')); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                   <a href="<?php echo e(route('orders.show', $order->id)); ?>" class="btn btn-info btn-sm" title="<?php echo e(__('messages.View')); ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo e(route('orders.edit', $order->id)); ?>" class="btn btn-primary btn-sm" title="<?php echo e(__('messages.Edit')); ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="row">
                            <div class="col-12">
                                <?php echo e($orders->appends(request()->query())->links()); ?>

                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="ri-shopping-cart-line font-size-48 text-muted"></i>
                            <h5 class="mt-3"><?php echo e(__('messages.no_orders_found')); ?></h5>
                            <p class="text-muted"><?php echo e(__('messages.no_orders_message')); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/orders/index.blade.php ENDPATH**/ ?>