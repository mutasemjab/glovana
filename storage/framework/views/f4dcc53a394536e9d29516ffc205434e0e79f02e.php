<?php $__env->startSection('title'); ?> 
<?php echo e(__('messages.dashboard')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
.dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 10px;
}

.card {
    background-color: white;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 25px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-left: 5px solid #007bff;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

.card.providers { border-left-color: #28a745; }
.card.salons { border-left-color: #17a2b8; }
.card.customers { border-left-color: #ffc107; }
.card.orders { border-left-color: #dc3545; }
.card.sales { border-left-color: #6f42c1; }
.card.complaints { border-left-color: #fd7e14; }
.card.low-stock { 
    border-left-color: #dc3545; 
    background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
}

.card.low-stock.warning {
    animation: pulse-warning 2s infinite;
}

.card h2 {
    font-size: 16px;
    color: #555;
    margin-bottom: 15px;
    font-weight: 600;
}

.card p {
    font-size: 28px;
    font-weight: bold;
    color: #333;
    margin: 0;
}

.card .small-text {
    font-size: 14px;
    color: #777;
    margin-top: 10px;
}

.status-breakdown {
    display: flex;
    justify-content: space-around;
    margin-top: 15px;
    font-size: 12px;
}

.status-item {
    text-align: center;
}

.status-number {
    font-weight: bold;
    font-size: 16px;
    display: block;
}

.active { color: #28a745; }
.completed { color: #007bff; }
.canceled { color: #dc3545; }

/* Low Stock Specific Styles */
.low-stock-warning {
    color: #dc3545;
    font-size: 24px;
    animation: blink 1.5s infinite;
}

.low-stock-list {
    max-height: 200px;
    overflow-y: auto;
    margin-top: 15px;
    text-align: left;
}

.low-stock-item {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 5px;
    padding: 8px 12px;
    margin-bottom: 8px;
    display: flex;
    justify-content: between;
    align-items: center;
}

.low-stock-item .product-name {
    font-weight: 600;
    color: #856404;
    flex: 1;
}

.low-stock-item .quantity {
    background: #dc3545;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    margin-left: 10px;
}

.warning-icon {
    color: #dc3545;
    font-size: 20px;
    margin-right: 8px;
    animation: shake 0.5s infinite;
}

.view-all-link {
    display: inline-block;
    margin-top: 10px;
    color: #007bff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.view-all-link:hover {
    color: #0056b3;
    text-decoration: underline;
}

/* Animations */
@keyframes pulse-warning {
    0% { box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
    50% { box-shadow: 0 4px 20px rgba(220, 53, 69, 0.3); }
    100% { box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.3; }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-2px); }
    75% { transform: translateX(2px); }
}

/* Notification Badge */
.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    font-size: 12px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: bounce 1s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-5px); }
    60% { transform: translateY(-3px); }
}

.card-header-with-badge {
    position: relative;
    display: inline-block;
}

@media (max-width: 768px) {
    .dashboard {
        grid-template-columns: 1fr;
        padding: 10px;
    }
    
    .card {
        padding: 20px;
    }
    
    .low-stock-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .low-stock-item .quantity {
        margin-left: 0;
        margin-top: 5px;
    }
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('contentheaderlink'); ?>
<a href="<?php echo e(route('admin.dashboard')); ?>">
    <?php echo e(__('messages.dashboard')); ?>

</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('contentheaderactive'); ?>
<?php echo e(__('messages.view')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Low Stock Alert Banner (if any products are low) -->
<?php if($lowStockCount > 0): ?>
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle warning-icon"></i>
        <div>
            <strong><?php echo e(__('messages.low_stock_warning')); ?>!</strong>
            <?php echo e(__('messages.products_below_minimum', ['count' => $lowStockCount, 'minimum' => $minimumQuantityThreshold])); ?>

        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="dashboard">
    <!-- Low Stock Products Warning Card -->
    <?php if($lowStockCount > 0): ?>
    <div class="card low-stock warning">
        <div class="card-header-with-badge">
            <h2>
                <i class="fas fa-exclamation-triangle warning-icon"></i>
                <?php echo e(__('messages.low_stock_products')); ?>

            </h2>
            <span class="notification-badge"><?php echo e($lowStockCount); ?></span>
        </div>
        <p class="low-stock-warning"><?php echo e($lowStockCount); ?></p>
        <div class="small-text"><?php echo e(__('messages.products_below_minimum_quantity', ['minimum' => $minimumQuantityThreshold])); ?></div>
        
        <?php if($lowStockProducts->count() > 0): ?>
        <div class="low-stock-list">
            <?php $__currentLoopData = $lowStockProducts->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="low-stock-item">
                <span class="product-name"><?php echo e($product->name); ?></span>
                <span class="quantity"><?php echo e($product->quantity); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            
            <?php if($lowStockProducts->count() > 5): ?>
            <a href="<?php echo e(route('products.index', ['low_stock' => 1])); ?>" class="view-all-link">
                <?php echo e(__('messages.view_all_low_stock_products')); ?>

            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Service Providers Count -->
    <div class="card providers">
        <h2><?php echo e(__('messages.providers_count')); ?></h2>
        <p><?php echo e(number_format($providersCount)); ?></p>
    </div>

    <!-- Salons Count -->
    <div class="card salons">
        <h2><?php echo e(__('messages.salons_count')); ?></h2>
        <p><?php echo e(number_format($salonsCount)); ?></p>
    </div>

    <!-- Customers Count -->
    <div class="card customers">
        <h2><?php echo e(__('messages.customers_count')); ?></h2>
        <p><?php echo e(number_format($usersCount)); ?></p>
    </div>

    <!-- Total Orders -->
    <div class="card orders">
        <h2><?php echo e(__('messages.total_orders')); ?></h2>
        <p><?php echo e(number_format($totalOrders)); ?></p>
        <div class="status-breakdown">
            <div class="status-item">
                <span class="status-number active"><?php echo e($activeOrders); ?></span>
                <div><?php echo e(__('messages.active')); ?></div>
            </div>
            <div class="status-item">
                <span class="status-number completed"><?php echo e($completedOrders); ?></span>
                <div><?php echo e(__('messages.completed')); ?></div>
            </div>
            <div class="status-item">
                <span class="status-number canceled"><?php echo e($canceledOrders); ?></span>
                <div><?php echo e(__('messages.canceled')); ?></div>
            </div>
        </div>
    </div>

    <!-- Total Appointments -->
    <div class="card orders">
        <h2><?php echo e(__('messages.total_appointments')); ?></h2>
        <p><?php echo e(number_format($totalAppointments)); ?></p>
        <div class="status-breakdown">
            <div class="status-item">
                <span class="status-number active"><?php echo e($activeAppointments); ?></span>
                <div><?php echo e(__('messages.active')); ?></div>
            </div>
            <div class="status-item">
                <span class="status-number completed"><?php echo e($completedAppointments); ?></span>
                <div><?php echo e(__('messages.completed')); ?></div>
            </div>
            <div class="status-item">
                <span class="status-number canceled"><?php echo e($canceledAppointments); ?></span>
                <div><?php echo e(__('messages.canceled')); ?></div>
            </div>
        </div>
    </div>

    <!-- Total Sales -->
    <div class="card sales">
        <h2><?php echo e(__('messages.total_sales')); ?></h2>
        <p><?php echo e(number_format($totalSales, 2)); ?> <?php echo e(__('messages.currency')); ?></p>
        <div class="small-text"><?php echo e(__('messages.completed_orders_only')); ?></div>
    </div>

    <!-- Late Requests/Complaints -->
    <div class="card complaints">
        <h2><?php echo e(__('messages.late_requests')); ?></h2>
        <p><?php echo e(number_format($totalLateRequests)); ?></p>
        <div class="small-text"><?php echo e(__('messages.requests_older_24h')); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>