

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> <?php echo e(__('messages.Orders_Report')); ?>

                    </h3>
                </div>

                <!-- Filter Form -->
                <div class="card-body border-bottom">
                    <form method="GET" action="<?php echo e(route('reports.orders.generate')); ?>" class="row g-3">
                        <!-- Date Range -->
                        <div class="col-md-3">
                            <label class="form-label"><?php echo e(__('messages.Start_Date')); ?> <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="start_date" 
                                   class="form-control" 
                                   value="<?php echo e(request('start_date', now()->subDays(30)->format('Y-m-d'))); ?>"
                                   required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><?php echo e(__('messages.End_Date')); ?> <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="end_date" 
                                   class="form-control" 
                                   value="<?php echo e(request('end_date', now()->format('Y-m-d'))); ?>"
                                   required>
                        </div>

                        <!-- Order Status Filter -->
                        <div class="col-md-3">
                            <label class="form-label"><?php echo e(__('messages.Order_Status')); ?></label>
                            <select name="order_status[]" class="form-control" multiple>
                                <option value="1" <?php echo e(in_array('1', request('order_status', [])) ? 'selected' : ''); ?>>
                                    <?php echo e(__('messages.Pending')); ?>

                                </option>
                                <option value="2" <?php echo e(in_array('2', request('order_status', [])) ? 'selected' : ''); ?>>
                                    <?php echo e(__('messages.Accepted')); ?>

                                </option>
                                <option value="3" <?php echo e(in_array('3', request('order_status', [])) ? 'selected' : ''); ?>>
                                    <?php echo e(__('messages.On_The_Way')); ?>

                                </option>
                                <option value="4" <?php echo e(in_array('4', request('order_status', [])) ? 'selected' : ''); ?>>
                                    <?php echo e(__('messages.Delivered')); ?>

                                </option>
                                <option value="5" <?php echo e(in_array('5', request('order_status', [])) ? 'selected' : ''); ?>>
                                    <?php echo e(__('messages.Canceled')); ?>

                                </option>
                                <option value="6" <?php echo e(in_array('6', request('order_status', [])) ? 'selected' : ''); ?>>
                                    <?php echo e(__('messages.Refund')); ?>

                                </option>
                            </select>
                        </div>

                        <!-- Payment Status -->
                        <div class="col-md-3">
                            <label class="form-label"><?php echo e(__('messages.Payment_Status')); ?></label>
                            <select name="payment_status" class="form-control">
                                <option value=""><?php echo e(__('messages.All')); ?></option>
                                <option value="1" <?php echo e(request('payment_status') == '1' ? 'selected' : ''); ?>>
                                    <?php echo e(__('messages.Paid')); ?>

                                </option>
                                <option value="2" <?php echo e(request('payment_status') == '2' ? 'selected' : ''); ?>>
                                    <?php echo e(__('messages.Unpaid')); ?>

                                </option>
                            </select>
                        </div>

                        <!-- Payment Type -->
                        <div class="col-md-3">
                            <label class="form-label"><?php echo e(__('messages.Payment_Type')); ?></label>
                            <select name="payment_type" class="form-control">
                                <option value=""><?php echo e(__('messages.All')); ?></option>
                                <?php if(isset($paymentTypes)): ?>
                                    <?php $__currentLoopData = $paymentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($type); ?>" <?php echo e(request('payment_type') == $type ? 'selected' : ''); ?>>
                                            <?php echo e(ucfirst($type)); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Customer Filter -->
                        <div class="col-md-3">
                            <label class="form-label"><?php echo e(__('messages.Customer')); ?></label>
                            <select name="user_id" class="form-control">
                                <option value=""><?php echo e(__('messages.All_Customers')); ?></option>
                                <?php if(isset($users)): ?>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>" <?php echo e(request('user_id') == $user->id ? 'selected' : ''); ?>>
                                            <?php echo e($user->name); ?> (<?php echo e($user->email); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Delivery Filter -->
                        <div class="col-md-3">
                            <label class="form-label"><?php echo e(__('messages.Delivery')); ?></label>
                            <select name="delivery_id" class="form-control">
                                <option value=""><?php echo e(__('messages.All_Deliveries')); ?></option>
                                <?php if(isset($deliveries)): ?>
                                    <?php $__currentLoopData = $deliveries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $delivery): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($delivery->id); ?>" <?php echo e(request('delivery_id') == $delivery->id ? 'selected' : ''); ?>>
                                            <?php echo e($delivery->place); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Report Type -->
                        <div class="col-md-3">
                            <label class="form-label"><?php echo e(__('messages.Report_Type')); ?></label>
                            <select name="report_type" class="form-control" required>
                                <option value="summary" <?php echo e(request('report_type', 'summary') == 'summary' ? 'selected' : ''); ?>>
                                    <?php echo e(__('messages.Summary_Report')); ?>

                                </option>
                                <option value="detailed" <?php echo e(request('report_type') == 'detailed' ? 'selected' : ''); ?>>
                                    <?php echo e(__('messages.Detailed_Report')); ?>

                                </option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> <?php echo e(__('messages.Generate_Report')); ?>

                            </button>
                            <a href="<?php echo e(route('reports.orders.index')); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> <?php echo e(__('messages.Clear')); ?>

                            </a>
                        </div>
                    </form>
                </div>

                <?php if(isset($summary)): ?>
                    <!-- Summary Statistics -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3">
                                    <i class="fas fa-chart-bar"></i> <?php echo e(__('messages.Summary_Statistics')); ?>

                                    <small class="text-muted">(<?php echo e($summary['date_range']); ?>)</small>
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Revenue Cards -->
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3><?php echo e(number_format($summary['total_revenue'], 2)); ?> <?php echo e(__('messages.Currency')); ?></h3>
                                        <p><?php echo e(__('messages.Total_Revenue')); ?></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3><?php echo e($summary['total_orders']); ?></h3>
                                        <p><?php echo e(__('messages.Total_Orders')); ?></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3><?php echo e(number_format($summary['average_order_value'], 2)); ?> <?php echo e(__('messages.Currency')); ?></h3>
                                        <p><?php echo e(__('messages.Average_Order_Value')); ?></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3><?php echo e(number_format($summary['total_discounts'] + $summary['total_coupon_discounts'], 2)); ?> <?php echo e(__('messages.Currency')); ?></h3>
                                        <p><?php echo e(__('messages.Total_Discounts')); ?></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-percent"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Status Breakdown -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><?php echo e(__('messages.Order_Status_Breakdown')); ?></h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <span class="badge bg-secondary p-2"><?php echo e($summary['pending_orders']); ?></span>
                                                    <br><small><?php echo e(__('messages.Pending')); ?></small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <span class="badge bg-primary p-2"><?php echo e($summary['accepted_orders']); ?></span>
                                                    <br><small><?php echo e(__('messages.Accepted')); ?></small>
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <div class="text-center">
                                                    <span class="badge bg-info p-2"><?php echo e($summary['on_the_way_orders']); ?></span>
                                                    <br><small><?php echo e(__('messages.On_The_Way')); ?></small>
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <div class="text-center">
                                                    <span class="badge bg-success p-2"><?php echo e($summary['delivered_orders']); ?></span>
                                                    <br><small><?php echo e(__('messages.Delivered')); ?></small>
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <div class="text-center">
                                                    <span class="badge bg-warning p-2"><?php echo e($summary['canceled_orders']); ?></span>
                                                    <br><small><?php echo e(__('messages.Canceled')); ?></small>
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <div class="text-center">
                                                    <span class="badge bg-danger p-2"><?php echo e($summary['refund_orders']); ?></span>
                                                    <br><small><?php echo e(__('messages.Refund')); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><?php echo e(__('messages.Payment_Status')); ?></h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <span class="badge bg-success p-3"><?php echo e($summary['paid_orders']); ?></span>
                                                    <br><small><?php echo e(__('messages.Paid')); ?></small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <span class="badge bg-danger p-3"><?php echo e($summary['unpaid_orders']); ?></span>
                                                    <br><small><?php echo e(__('messages.Unpaid')); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daily Stats Chart (for summary reports) -->
                        <?php if(request('report_type', 'summary') == 'summary' && isset($dailyStats) && $dailyStats->isNotEmpty()): ?>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><?php echo e(__('messages.Daily_Statistics')); ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th><?php echo e(__('messages.Date')); ?></th>
                                                            <th><?php echo e(__('messages.Orders')); ?></th>
                                                            <th><?php echo e(__('messages.Revenue')); ?></th>
                                                            <th><?php echo e(__('messages.Avg_Order_Value')); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $__currentLoopData = $dailyStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <td><?php echo e($stat->order_date); ?></td>
                                                                <td><?php echo e($stat->orders_count); ?></td>
                                                                <td><?php echo e(number_format($stat->daily_revenue, 2)); ?> <?php echo e(__('messages.Currency')); ?></td>
                                                                <td><?php echo e(number_format($stat->avg_order_value, 2)); ?> <?php echo e(__('messages.Currency')); ?></td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Top Products (for summary reports) -->
                        <?php if(request('report_type', 'summary') == 'summary' && isset($topProducts) && $topProducts->isNotEmpty()): ?>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><?php echo e(__('messages.Top_Products')); ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th><?php echo e(__('messages.Product')); ?></th>
                                                            <th><?php echo e(__('messages.Quantity_Sold')); ?></th>
                                                            <th><?php echo e(__('messages.Revenue')); ?></th>
                                                            <th><?php echo e(__('messages.Orders')); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <td><?php echo e(app()->getLocale() == 'ar' ? $product->name_ar : $product->name_en); ?></td>
                                                                <td><?php echo e($product->total_quantity); ?></td>
                                                                <td><?php echo e(number_format($product->total_revenue, 2)); ?> <?php echo e(__('messages.Currency')); ?></td>
                                                                <td><?php echo e($product->orders_count); ?></td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Export Button -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <form method="GET" action="<?php echo e(route('reports.orders.export')); ?>" class="d-inline">
                                    <input type="hidden" name="start_date" value="<?php echo e(request('start_date')); ?>">
                                    <input type="hidden" name="end_date" value="<?php echo e(request('end_date')); ?>">
                                    <?php $__currentLoopData = request('order_status', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <input type="hidden" name="order_status[]" value="<?php echo e($status); ?>">
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(request('payment_status')): ?>
                                        <input type="hidden" name="payment_status" value="<?php echo e(request('payment_status')); ?>">
                                    <?php endif; ?>
                                    <?php if(request('payment_type')): ?>
                                        <input type="hidden" name="payment_type" value="<?php echo e(request('payment_type')); ?>">
                                    <?php endif; ?>
                                    <?php if(request('user_id')): ?>
                                        <input type="hidden" name="user_id" value="<?php echo e(request('user_id')); ?>">
                                    <?php endif; ?>
                                    <?php if(request('delivery_id')): ?>
                                        <input type="hidden" name="delivery_id" value="<?php echo e(request('delivery_id')); ?>">
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-download"></i> <?php echo e(__('messages.Export_CSV')); ?>

                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Detailed Orders Table -->
                <?php if(isset($orders) && $orders->isNotEmpty()): ?>
                    <div class="card-body">
                        <h5 class="mb-3"><?php echo e(__('messages.Detailed_Orders')); ?></h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('messages.Order_Number')); ?></th>
                                        <th><?php echo e(__('messages.Date')); ?></th>
                                        <th><?php echo e(__('messages.Customer')); ?></th>
                                        <th><?php echo e(__('messages.Status')); ?></th>
                                        <th><?php echo e(__('messages.Payment')); ?></th>
                                        <th><?php echo e(__('messages.Delivery')); ?></th>
                                        <th><?php echo e(__('messages.Total')); ?></th>
                                        <th><?php echo e(__('messages.Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo e($order->number); ?></strong>
                                            </td>
                                            <td><?php echo e(Carbon\Carbon::parse($order->date)->format('Y-m-d H:i')); ?></td>
                                            <td>
                                                <div>
                                                    <strong><?php echo e($order->customer_name); ?></strong>
                                                    <br><small class="text-muted"><?php echo e($order->customer_email); ?></small>
                                                    <?php if($order->customer_phone): ?>
                                                        <br><small class="text-muted"><?php echo e($order->customer_phone); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php switch($order->order_status):
                                                    case (1): ?>
                                                        <span class="badge bg-secondary"><?php echo e(__('messages.Pending')); ?></span>
                                                        <?php break; ?>
                                                    <?php case (2): ?>
                                                        <span class="badge bg-primary"><?php echo e(__('messages.Accepted')); ?></span>
                                                        <?php break; ?>
                                                    <?php case (3): ?>
                                                        <span class="badge bg-info"><?php echo e(__('messages.On_The_Way')); ?></span>
                                                        <?php break; ?>
                                                    <?php case (4): ?>
                                                        <span class="badge bg-success"><?php echo e(__('messages.Delivered')); ?></span>
                                                        <?php break; ?>
                                                    <?php case (5): ?>
                                                        <span class="badge bg-warning"><?php echo e(__('messages.Canceled')); ?></span>
                                                        <?php break; ?>
                                                    <?php case (6): ?>
                                                        <span class="badge bg-danger"><?php echo e(__('messages.Refund')); ?></span>
                                                        <?php break; ?>
                                                <?php endswitch; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <?php if($order->payment_status == 1): ?>
                                                        <span class="badge bg-success"><?php echo e(__('messages.Paid')); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger"><?php echo e(__('messages.Unpaid')); ?></span>
                                                    <?php endif; ?>
                                                    <br><small class="text-muted"><?php echo e(ucfirst($order->payment_type)); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($order->delivery_place): ?>
                                                    <span class="badge bg-info"><?php echo e($order->delivery_place); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo e(number_format($order->total_prices, 2)); ?> <?php echo e(__('messages.Currency')); ?></strong>
                                                    <?php if($order->total_discounts > 0): ?>
                                                        <br><small class="text-success">-<?php echo e(number_format($order->total_discounts, 2)); ?> <?php echo e(__('messages.Discount')); ?></small>
                                                    <?php endif; ?>
                                                    <?php if($order->delivery_fee > 0): ?>
                                                        <br><small class="text-info">+<?php echo e(number_format($order->delivery_fee, 2)); ?> <?php echo e(__('messages.Delivery')); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="<?php echo e(route('orders.show', $order->id)); ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> <?php echo e(__('messages.View')); ?>

                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            <?php echo e($orders->links()); ?>

                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when dates change for quick filtering
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    
    if (startDate && endDate) {
        startDate.addEventListener('change', function() {
            if (endDate.value && this.value) {
                // Auto-submit could be enabled here if desired
                // document.querySelector('form').submit();
            }
        });
    }
    
    // Set max date for start_date to end_date value
    if (startDate && endDate) {
        endDate.addEventListener('change', function() {
            startDate.max = this.value;
        });
        
        startDate.addEventListener('change', function() {
            endDate.min = this.value;
        });
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/reports/ordersReport.blade.php ENDPATH**/ ?>