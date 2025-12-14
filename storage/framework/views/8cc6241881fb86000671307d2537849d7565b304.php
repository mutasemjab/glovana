<!-- Main Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="brand-link">
        <img src="<?php echo e(asset('assets/admin/dist/img/AdminLTELogo.png')); ?>" alt="App Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Glovana</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <h4 style="color: white; margin:auto;"> <?php echo e(auth()->user()->name); ?></h4>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.dashboard')); ?>"
                        class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p><?php echo e(__('messages.dashboard')); ?></p>
                    </a>
                </li>

                <!-- User Management Section -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['user-table', 'user-add', 'user-edit', 'user-delete', 'driver-table', 'driver-add',
                    'driver-edit', 'driver-delete'])): ?>
                    <li
                        class="nav-item <?php echo e(request()->is('admin/users*') || request()->is('admin/drivers*') || request()->is('admin/provider-details*') ? 'menu-open' : ''); ?>">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>
                                <?php echo e(__('messages.user_management')); ?>

                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['user-table', 'user-add', 'user-edit', 'user-delete'])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('users.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('users.index') ? 'active' : ''); ?>">
                                        <i class="far fa-user nav-icon"></i>
                                        <p><?php echo e(__('messages.users')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['provider-table', 'provider-add', 'provider-edit', 'provider-delete'])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('providers.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('providers.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-car nav-icon"></i>
                                        <p><?php echo e(__('messages.providers')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Catalog Management (NEW SECTION) -->
                <li
                    class="nav-item <?php echo e(request()->is('services*') || request()->is('deliveries*') || request()->is('types*') || request()->is('categories*') || request()->is('products*') ? 'menu-open' : ''); ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-tags"></i>
                        <p>
                            <?php echo e(__('messages.Catalog_Management')); ?>

                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php echo e(route('services.index')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('services.*') ? 'active' : ''); ?>">
                                <i class="fas fa-handshake nav-icon"></i>
                                <p><?php echo e(__('messages.Services')); ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo e(route('types.index')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('types.*') ? 'active' : ''); ?>">
                                <i class="fas fa-list nav-icon"></i>
                                <p><?php echo e(__('messages.Types')); ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo e(route('categories.index')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('categories.*') ? 'active' : ''); ?>">
                                <i class="fas fa-folder nav-icon"></i>
                                <p><?php echo e(__('messages.Categories')); ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo e(route('products.index')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('products.*') ? 'active' : ''); ?>">
                                <i class="fas fa-box nav-icon"></i>
                                <p><?php echo e(__('messages.Products')); ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo e(route('note-vouchers.index')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('note-vouchers.*') ? 'active' : ''); ?>">
                                <i class="fas fa-box nav-icon"></i>
                                <p><?php echo e(__('messages.note_vouchers')); ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo e(route('deliveries.index')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('deliveries.*') ? 'active' : ''); ?>">
                                <i class="fas fa-truck nav-icon"></i>
                                <p><?php echo e(__('messages.Deliveries')); ?></p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Services & Coupons -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['order-table', 'order-add', 'order-edit', 'order-delete', 'coupon-table', 'coupon-add',
                    'coupon-edit', 'coupon-delete'])): ?>
                    <li
                        class="nav-item <?php echo e(request()->is('admin/orders*') || request()->is('admin/coupons*') ? 'menu-open' : ''); ?>">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-concierge-bell"></i>
                            <p>
                                <?php echo e(__('messages.order_management')); ?>

                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['order-table', 'order-add', 'order-edit', 'order-delete'])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('orders.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('orders.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-handshake nav-icon"></i>
                                        <p><?php echo e(__('messages.orders')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['appointment-table', 'appointment-add', 'appointment-edit', 'appointment-delete'])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('appointments.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('appointments.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-handshake nav-icon"></i>
                                        <p><?php echo e(__('messages.appointments')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>

                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['coupon-table', 'coupon-add', 'coupon-edit', 'coupon-delete'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('coupons.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('coupons.index') ? 'active' : ''); ?>">
                            <i class="fas fa-ticket-alt nav-icon"></i>
                            <p><?php echo e(__('messages.coupons')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Notifications -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['notification-table', 'notification-add', 'notification-edit', 'notification-delete'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('notifications.create')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('notifications.create') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-bell"></i>
                            <p><?php echo e(__('messages.notifications')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Content Management -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['page-table', 'page-add', 'page-edit', 'page-delete'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('pages.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('pages.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p><?php echo e(__('messages.pages')); ?></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo e(route('banners.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('banners.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p><?php echo e(__('messages.banners')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['wallet-table', 'wallet-add', 'wallet-edit', 'wallet-delete'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('wallet_transactions.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('wallet_transactions.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p><?php echo e(__('messages.Wallet Transactions')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['withdrawal-table', 'withdrawal-add', 'withdrawal-edit', 'withdrawal-delete'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('withdrawals.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('withdrawals.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p><?php echo e(__('messages.withdrawals')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>
             
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['rating-table', 'rating-add', 'rating-edit', 'rating-delete'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.ratings.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.ratings.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p><?php echo e(__('messages.ratings')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>
          
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['vip-subscription-table', 'vip-subscription-add', 'vip-subscription-edit', 'vip-subscription-delete'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.vip-subscriptions.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.vip-subscriptions.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p><?php echo e(__('messages.vip_subscriptions')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>
          
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['fines-discount-table', 'fines-discount-add', 'fines-discount-edit', 'fines-discount-delete'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('fines-discounts.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('fines-discounts.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p><?php echo e(__('messages.fines_discounts')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>
    
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['coupon-table', 'coupon-add', 'coupon-edit', 'coupon-delete'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('usedCoupons.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('usedCoupons.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p><?php echo e(__('messages.coupons_and_users')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>
             
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['deleteRequest-table', 'deleteRequest-add', 'deleteRequest-edit', 'deleteRequest-delete'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.provider-delete-requests.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.provider-delete-requests.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p><?php echo e(__('messages.delete_account_requests')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>



                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['payment-table', 'payment-add', 'payment-edit', 'payment-delete'])): ?>
                    <li class="nav-item <?php echo e(request()->is('admin/payment-report*') ? 'menu-open' : ''); ?>">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-concierge-bell"></i>
                            <p>
                                <?php echo e(__('messages.reports')); ?>

                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['points-report'])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('reports.points')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('reports.points') ? 'active' : ''); ?>">
                                        <i class="fas fa-handshake nav-icon"></i>
                                        <p><?php echo e(__('messages.Points_Reports')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['payment-report'])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('admin.payment.report')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('admin.payment.report') ? 'active' : ''); ?>">
                                        <i class="fas fa-handshake nav-icon"></i>
                                        <p><?php echo e(__('messages.Payment Report')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['provider-report' ])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('admin.providers.report.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('admin.providers.report.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-handshake nav-icon"></i>
                                        <p><?php echo e(__('messages.provider Report')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['orderReport-report'])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('reports.orders.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('reports.orders.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-handshake nav-icon"></i>
                                        <p><?php echo e(__('messages.Orders Report')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['inventoryReport-report' ])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('reports.inventory.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('reports.inventory.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-handshake nav-icon"></i>
                                        <p><?php echo e(__('messages.Inventory_Report')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>



                        </ul>
                    </li>
                <?php endif; ?>





                <!-- System Settings -->
                <li
                    class="nav-item <?php echo e(request()->is('admin/settings*') || request()->is('admin/roles*') || request()->is('admin/employees*') ? 'menu-open' : ''); ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            <?php echo e(__('messages.system_settings')); ?>

                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php echo e(route('settings.index')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('settings.index') ? 'active' : ''); ?>">
                                <i class="fas fa-wrench nav-icon"></i>
                                <p><?php echo e(__('messages.general_settings')); ?></p>
                            </a>
                        </li>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['role-table', 'role-add', 'role-edit', 'role-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.role.index')); ?>"
                                    class="nav-link <?php echo e(request()->routeIs('admin.role.index') ? 'active' : ''); ?>">
                                    <i class="fas fa-user-shield nav-icon"></i>
                                    <p><?php echo e(__('messages.roles')); ?></p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['employee-table', 'employee-add', 'employee-edit', 'employee-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.employee.index')); ?>"
                                    class="nav-link <?php echo e(request()->routeIs('admin.employee.index') ? 'active' : ''); ?>">
                                    <i class="fas fa-user-tie nav-icon"></i>
                                    <p><?php echo e(__('messages.employees')); ?></p>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Account -->
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.login.edit', auth()->user()->id)); ?>"
                        class="nav-link <?php echo e(request()->routeIs('admin.login.edit') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p><?php echo e(__('messages.admin_account')); ?></p>
                    </a>
                </li>
          
                 <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['activity-logs-table', 'activity-logs-add', 'activity-logs-edit', 'activity-logs-delete'])): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.activity-logs.index')); ?>"
                        class="nav-link <?php echo e(request()->routeIs('admin.activity-logs.index') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p><?php echo e(__('messages.activity-logs')); ?></p>
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </nav>
    </div>
</aside>
<?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/includes/sidebar.blade.php ENDPATH**/ ?>