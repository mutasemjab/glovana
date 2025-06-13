

<?php $__env->startSection('title', __('messages.appointments')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?php echo e(__('messages.appointments')); ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('messages.dashboard')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo e(__('messages.appointments')); ?></li>
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
                            <span class="text-muted mb-3 lh-1 d-block text-truncate"><?php echo e(__('messages.total_appointments')); ?></span>
                            <h4 class="mb-3"><?php echo e(number_format($statistics['total_appointments'])); ?></h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title bg-primary rounded-circle">
                                    <i class="ri-calendar-line font-size-24"></i>
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
                            <span class="text-muted mb-3 lh-1 d-block text-truncate"><?php echo e(__('messages.pending_appointments')); ?></span>
                            <h4 class="mb-3"><?php echo e(number_format($statistics['pending_appointments'])); ?></h4>
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
                            <span class="text-muted mb-3 lh-1 d-block text-truncate"><?php echo e(__('messages.completed_appointments')); ?></span>
                            <h4 class="mb-3"><?php echo e(number_format($statistics['completed_appointments'])); ?></h4>
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
                            <span class="text-muted mb-3 lh-1 d-block text-truncate"><?php echo e(__('messages.vip_appointments')); ?></span>
                            <h4 class="mb-3"><?php echo e(number_format($statistics['vip_appointments'])); ?></h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-info">
                                <span class="avatar-title bg-info rounded-circle">
                                    <i class="ri-vip-crown-line font-size-24"></i>
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
                    <form method="GET" action="<?php echo e(route('appointments.index')); ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label"><?php echo e(__('messages.appointment_status')); ?></label>
                                <select name="appointment_status" class="form-control">
                                    <option value=""><?php echo e(__('messages.all_statuses')); ?></option>
                                    <option value="1" <?php echo e(request('appointment_status') == '1' ? 'selected' : ''); ?>><?php echo e(__('messages.pending')); ?></option>
                                    <option value="2" <?php echo e(request('appointment_status') == '2' ? 'selected' : ''); ?>><?php echo e(__('messages.accepted')); ?></option>
                                    <option value="3" <?php echo e(request('appointment_status') == '3' ? 'selected' : ''); ?>><?php echo e(__('messages.on_the_way')); ?></option>
                                    <option value="4" <?php echo e(request('appointment_status') == '4' ? 'selected' : ''); ?>><?php echo e(__('messages.delivered')); ?></option>
                                    <option value="5" <?php echo e(request('appointment_status') == '5' ? 'selected' : ''); ?>><?php echo e(__('messages.canceled')); ?></option>
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
                            <div class="col-md-3">
                                <label class="form-label"><?php echo e(__('messages.provider_type')); ?></label>
                                <select name="provider_type_id" class="form-control">
                                    <option value=""><?php echo e(__('messages.all_providers')); ?></option>
                                    <?php $__currentLoopData = $providerTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $providerType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($providerType->id); ?>" <?php echo e(request('provider_type_id') == $providerType->id ? 'selected' : ''); ?>>
                                            <?php echo e($providerType->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><?php echo e(__('messages.vip_status')); ?></label>
                                <select name="is_vip" class="form-control">
                                    <option value=""><?php echo e(__('messages.all_types')); ?></option>
                                    <option value="1" <?php echo e(request('is_vip') == '1' ? 'selected' : ''); ?>><?php echo e(__('messages.vip_only')); ?></option>
                                    <option value="2" <?php echo e(request('is_vip') == '2' ? 'selected' : ''); ?>><?php echo e(__('messages.regular_only')); ?></option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><?php echo e(__('messages.from_date')); ?></label>
                                <input type="date" name="from_date" class="form-control" value="<?php echo e(request('from_date')); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><?php echo e(__('messages.to_date')); ?></label>
                                <input type="date" name="to_date" class="form-control" value="<?php echo e(request('to_date')); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo e(__('messages.search')); ?></label>
                                <input type="text" name="search" class="form-control" placeholder="<?php echo e(__('messages.search_appointments')); ?>" value="<?php echo e(request('search')); ?>">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-search-line"></i> <?php echo e(__('messages.filter')); ?>

                                </button>
                                <a href="<?php echo e(route('appointments.index')); ?>" class="btn btn-secondary">
                                    <i class="ri-refresh-line"></i> <?php echo e(__('messages.reset')); ?>

                                </a>
                              
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><?php echo e(__('messages.appointments_list')); ?></h4>
                </div>
                <div class="card-body">
                    <?php if($appointments->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th><?php echo e(__('messages.appointment_number')); ?></th>
                                        <th><?php echo e(__('messages.customer')); ?></th>
                                        <th><?php echo e(__('messages.provider')); ?></th>
                                        <th><?php echo e(__('messages.service_type')); ?></th>
                                        <th><?php echo e(__('messages.total_amount')); ?></th>
                                        <th><?php echo e(__('messages.appointment_status')); ?></th>
                                        <th><?php echo e(__('messages.payment_status')); ?></th>
                                        <th><?php echo e(__('messages.vip_status')); ?></th>
                                        <th><?php echo e(__('messages.appointment_date')); ?></th>
                                        <th><?php echo e(__('messages.actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><strong>#<?php echo e($appointment->number); ?></strong></td>
                                            <td>
                                                <div><?php echo e($appointment->user->name ?? __('messages.no_customer')); ?></div>
                                                <small class="text-muted"><?php echo e($appointment->user->phone ?? ''); ?></small>
                                            </td>
                                            <td>
                                                <div><?php echo e($appointment->providerType->provider->name_of_manager ?? __('messages.no_provider')); ?></div>
                                                <small class="text-muted"><?php echo e($appointment->providerType->name ?? ''); ?></small>
                                            </td>
                                            <td><?php echo e($appointment->providerType->type->name ?? __('messages.no_type')); ?></td>
                                            <td><?php echo e(number_format($appointment->total_prices, 2)); ?> <?php echo e(__('messages.jd')); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo e($appointment->appointment_status == 1 ? 'warning' : 
                                                    ($appointment->appointment_status == 2 ? 'info' : 
                                                    ($appointment->appointment_status == 3 ? 'primary' : 
                                                    ($appointment->appointment_status == 4 ? 'success' : 'danger')))); ?>">
                                                    <?php echo e($appointment->appointment_status_label); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo e($appointment->payment_status == 1 ? 'bg-success' : 'bg-warning'); ?>">
                                                    <?php echo e($appointment->payment_status_label); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo e($appointment->providerType->is_vip == 1 ? 'bg-warning' : 'bg-secondary'); ?>">
                                                    <?php echo e($appointment->is_vip_label); ?>

                                                </span>
                                            </td>
                                            <td><?php echo e($appointment->date->format('Y-m-d H:i')); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo e(route('appointments.show', $appointment->id)); ?>" class="btn btn-sm btn-info">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="<?php echo e(route('appointments.edit', $appointment->id)); ?>" class="btn btn-sm btn-primary">
                                                        <i class="ri-edit-line"></i>
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
                                <?php echo e($appointments->appends(request()->query())->links()); ?>

                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="ri-calendar-line font-size-48 text-muted"></i>
                            <h5 class="mt-3"><?php echo e(__('messages.no_appointments_found')); ?></h5>
                            <p class="text-muted"><?php echo e(__('messages.no_appointments_message')); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/appointments/index.blade.php ENDPATH**/ ?>