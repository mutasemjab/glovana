

<?php $__env->startSection('title', __('messages.delete_account_requests')); ?>

<?php $__env->startSection('css'); ?>
<style>
    .bg-gold {
        background-color: #ffd700 !important;
        color: #000 !important;
    }
    
    .modal-xl {
        max-width: 1200px;
    }
    
    .table-responsive {
        border-radius: 0.375rem;
    }
    
    .progress {
        background-color: #e9ecef;
    }
    
    @media (max-width: 768px) {
        .modal-xl {
            max-width: 95%;
        }
        
        .table-responsive {
            font-size: 0.875rem;
        }
    }
</style>
<!-- Add SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('messages.provider_delete_requests')); ?></h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="deleteRequestsTable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('messages.id')); ?></th>
                                    <th><?php echo e(__('messages.provider_info')); ?></th>
                                    <th><?php echo e(__('messages.contact_info')); ?></th>
                                    <th><?php echo e(__('messages.business_stats')); ?></th>
                                    <th><?php echo e(__('messages.status')); ?></th>
                                    <th><?php echo e(__('messages.request_date')); ?></th>
                                    <th><?php echo e(__('messages.actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $deleteRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($request->provider->id); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if($request->provider->photo_url): ?>
                                                <img src="<?php echo e($request->provider->photo_url); ?>" 
                                                     alt="<?php echo e($request->provider->name_of_manager); ?>" 
                                                     class="rounded-circle me-2" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo e($request->provider->name_of_manager); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo e(__('messages.member_since')); ?>: <?php echo e($request->provider->created_at->format('M Y')); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <i class="fas fa-phone text-primary"></i> <?php echo e($request->provider->country_code); ?><?php echo e($request->provider->phone); ?>

                                        </div>
                                        <?php if($request->provider->email): ?>
                                            <div class="mt-1">
                                                <i class="fas fa-envelope text-info"></i> <?php echo e($request->provider->email); ?>

                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <small class="text-muted"><?php echo e(__('messages.balance')); ?>:</small> 
                                            <strong class="text-success">JD <?php echo e(number_format($request->provider->balance, 2)); ?></strong>
                                        </div>
                                        <div>
                                            <small class="text-muted"><?php echo e(__('messages.points')); ?>:</small> 
                                            <strong class="text-warning"><?php echo e($request->provider->total_points); ?></strong>
                                        </div>
                                        <div>
                                            <small class="text-muted"><?php echo e(__('messages.services')); ?>:</small> 
                                            <strong><?php echo e($request->provider->providerTypes->count()); ?></strong>
                                        </div>
                                        <div>
                                            <small class="text-muted"><?php echo e(__('messages.appointments')); ?>:</small> 
                                            <strong><?php echo e($request->provider->appointments->count()); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                            $statusClass = $request->provider->activate == 1 ? 'success' : ($request->provider->activate == 2 ? 'danger' : 'warning');
                                            $statusText = $request->provider->activate == 1 ? __('messages.active') : ($request->provider->activate == 2 ? __('messages.inactive') : __('messages.waiting_approval'));
                                        ?>
                                        <span class="badge bg-<?php echo e($statusClass); ?>"><?php echo e($statusText); ?></span>
                                    </td>
                                    <td>
                                        <?php echo e($request->created_at->format('Y-m-d H:i')); ?>

                                        <br>
                                        <small class="text-muted"><?php echo e($request->created_at->diffForHumans()); ?></small>
                                    </td>
                                    <td>
                                        <div class="mt-1">
                                            <button type="button" class="btn btn-success btn-sm me-1" onclick="approveDelete(<?php echo e($request->id); ?>)">
                                                <i class="fas fa-check"></i> <?php echo e(__('messages.approve')); ?>

                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="rejectDelete(<?php echo e($request->id); ?>)">
                                                <i class="fas fa-times"></i> <?php echo e(__('messages.reject')); ?>

                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Detailed View Modal -->
                                <div class="modal fade" id="viewDetailsModal<?php echo e($request->id); ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><?php echo e(__('messages.provider_deletion_details')); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <!-- Basic Information -->
                                                    <div class="col-md-6">
                                                        <div class="card h-100">
                                                            <div class="card-header">
                                                                <h6 class="mb-0"><?php echo e(__('messages.basic_information')); ?></h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="text-center mb-3">
                                                                    <?php if($request->provider->photo_url): ?>
                                                                        <img src="<?php echo e($request->provider->photo_url); ?>" 
                                                                             alt="<?php echo e($request->provider->name_of_manager); ?>" 
                                                                             class="rounded-circle" 
                                                                             style="width: 100px; height: 100px; object-fit: cover;">
                                                                    <?php else: ?>
                                                                        <div class="bg-secondary rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                                                                             style="width: 100px; height: 100px;">
                                                                            <i class="fas fa-user fa-3x text-white"></i>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <table class="table table-sm">
                                                                    <tr>
                                                                        <td><strong><?php echo e(__('messages.name')); ?>:</strong></td>
                                                                        <td><?php echo e($request->provider->name_of_manager); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong><?php echo e(__('messages.phone')); ?>:</strong></td>
                                                                        <td><?php echo e($request->provider->country_code); ?><?php echo e($request->provider->phone); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong><?php echo e(__('messages.email')); ?>:</strong></td>
                                                                        <td><?php echo e($request->provider->email ?: __('messages.not_provided')); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong><?php echo e(__('messages.joined_date')); ?>:</strong></td>
                                                                        <td><?php echo e($request->provider->created_at->format('Y-m-d')); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong><?php echo e(__('messages.status')); ?>:</strong></td>
                                                                        <td>
                                                                            <span class="badge bg-<?php echo e($statusClass); ?>"><?php echo e($statusText); ?></span>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Financial Information -->
                                                    <div class="col-md-6">
                                                        <div class="card h-100">
                                                            <div class="card-header">
                                                                <h6 class="mb-0"><?php echo e(__('messages.financial_information')); ?></h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row text-center">
                                                                    <div class="col-6">
                                                                        <div class="border rounded p-3 mb-3">
                                                                            <h4 class="text-success">JD <?php echo e(number_format($request->provider->balance, 2)); ?></h4>
                                                                            <small class="text-muted"><?php echo e(__('messages.current_balance')); ?></small>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <div class="border rounded p-3 mb-3">
                                                                            <h4 class="text-warning"><?php echo e(number_format($request->provider->total_points)); ?></h4>
                                                                            <small class="text-muted"><?php echo e(__('messages.total_points')); ?></small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <?php if($request->provider->walletTransactions->isNotEmpty()): ?>
                                                                    <h6><?php echo e(__('messages.recent_wallet_transactions')); ?></h6>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th><?php echo e(__('messages.date')); ?></th>
                                                                                    <th><?php echo e(__('messages.amount')); ?></th>
                                                                                    <th><?php echo e(__('messages.type')); ?></th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php $__currentLoopData = $request->provider->walletTransactions->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                    <tr>
                                                                                        <td><?php echo e($transaction->created_at->format('Y-m-d')); ?></td>
                                                                                        <td class="<?php echo e($transaction->amount > 0 ? 'text-success' : 'text-danger'); ?>">
                                                                                            JD <?php echo e(number_format($transaction->amount, 2)); ?>

                                                                                        </td>
                                                                                        <td><?php echo e($transaction->type); ?></td>
                                                                                    </tr>
                                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Services Information -->
                                                    <div class="col-md-12 mt-3">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h6 class="mb-0"><?php echo e(__('messages.services_information')); ?></h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <?php if($request->provider->providerTypes->isNotEmpty()): ?>
                                                                    <div class="row">
                                                                        <?php $__currentLoopData = $request->provider->providerTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $providerType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <div class="col-md-6 mb-3">
                                                                                <div class="border rounded p-3">
                                                                                    <h6><?php echo e($providerType->name); ?></h6>
                                                                                    <p class="text-muted small"><?php echo e(Str::limit($providerType->description, 100)); ?></p>
                                                                                    <div class="row">
                                                                                        <div class="col-6">
                                                                                            <small><strong><?php echo e(__('messages.status')); ?>:</strong> 
                                                                                                <span class="badge badge-sm bg-<?php echo e($providerType->status == 1 ? 'success' : 'danger'); ?>">
                                                                                                    <?php echo e($providerType->status == 1 ? __('messages.active') : __('messages.inactive')); ?>

                                                                                                </span>
                                                                                            </small>
                                                                                        </div>
                                                                                    </div>
                                                                                    <?php if($providerType->is_vip == 1): ?>
                                                                                        <span class="badge bg-gold mt-2"><?php echo e(__('messages.vip_service')); ?></span>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            </div>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <p class="text-muted"><?php echo e(__('messages.no_services_registered')); ?></p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Appointments History -->
                                                    <div class="col-md-12 mt-3">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h6 class="mb-0"><?php echo e(__('messages.appointments_history')); ?></h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <?php if($request->provider->appointments->isNotEmpty()): ?>
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-3">
                                                                            <div class="text-center">
                                                                                <h5 class="text-primary"><?php echo e($request->provider->appointments->count()); ?></h5>
                                                                                <small><?php echo e(__('messages.total_appointments')); ?></small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <div class="text-center">
                                                                                <h5 class="text-success"><?php echo e($request->provider->appointments->where('appointment_status', 4)->count()); ?></h5>
                                                                                <small><?php echo e(__('messages.completed')); ?></small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <div class="text-center">
                                                                                <h5 class="text-warning"><?php echo e($request->provider->appointments->where('appointment_status', 1)->count()); ?></h5>
                                                                                <small><?php echo e(__('messages.pending')); ?></small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <div class="text-center">
                                                                                <h5 class="text-danger"><?php echo e($request->provider->appointments->where('appointment_status', 5)->count()); ?></h5>
                                                                                <small><?php echo e(__('messages.cancelled')); ?></small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <h6><?php echo e(__('messages.recent_appointments')); ?></h6>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th><?php echo e(__('messages.appointment_number')); ?></th>
                                                                                    <th><?php echo e(__('messages.date')); ?></th>
                                                                                    <th><?php echo e(__('messages.total_price')); ?></th>
                                                                                    <th><?php echo e(__('messages.status')); ?></th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php $__currentLoopData = $request->provider->appointments->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                    <tr>
                                                                                        <td><?php echo e($appointment->number); ?></td>
                                                                                        <td><?php echo e($appointment->date ? \Carbon\Carbon::parse($appointment->date)->format('Y-m-d H:i') : '-'); ?></td>
                                                                                        <td>JD <?php echo e(number_format($appointment->total_prices, 2)); ?></td>
                                                                                        <td>
                                                                                            <?php
                                                                                                $appointmentStatuses = [
                                                                                                    1 => ['text' => __('messages.pending'), 'class' => 'warning'],
                                                                                                    2 => ['text' => __('messages.accepted'), 'class' => 'info'],
                                                                                                    3 => ['text' => __('messages.on_the_way'), 'class' => 'primary'],
                                                                                                    4 => ['text' => __('messages.delivered'), 'class' => 'success'],
                                                                                                    5 => ['text' => __('messages.cancelled'), 'class' => 'danger'],
                                                                                                    6 => ['text' => __('messages.started'), 'class' => 'secondary'],
                                                                                                    7 => ['text' => __('messages.arrived'), 'class' => 'dark']
                                                                                                ];
                                                                                                $status = $appointmentStatuses[$appointment->appointment_status] ?? ['text' => 'Unknown', 'class' => 'secondary'];
                                                                                            ?>
                                                                                            <span class="badge bg-<?php echo e($status['class']); ?>"><?php echo e($status['text']); ?></span>
                                                                                        </td>
                                                                                    </tr>
                                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <p class="text-muted"><?php echo e(__('messages.no_appointments_found')); ?></p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Ratings Information -->
                                                    <?php
                                                        $allRatings = collect();
                                                        foreach($request->provider->providerTypes as $providerType) {
                                                            $allRatings = $allRatings->merge($providerType->ratings);
                                                        }
                                                        $averageRating = $allRatings->isNotEmpty() ? $allRatings->avg('rating') : 0;
                                                    ?>
                                                    <?php if($allRatings->isNotEmpty()): ?>
                                                        <div class="col-md-12 mt-3">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h6 class="mb-0"><?php echo e(__('messages.ratings_reviews')); ?></h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-4 text-center">
                                                                            <h3 class="text-warning"><?php echo e(number_format($averageRating, 1)); ?></h3>
                                                                            <div class="mb-2">
                                                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                                                    <i class="fas fa-star <?php echo e($i <= $averageRating ? 'text-warning' : 'text-muted'); ?>"></i>
                                                                                <?php endfor; ?>
                                                                            </div>
                                                                            <small><?php echo e(__('messages.from')); ?> <?php echo e($allRatings->count()); ?> <?php echo e(__('messages.reviews')); ?></small>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                                            <?php for($i = 5; $i >= 1; $i--): ?>
                                                                                <?php
                                                                                    $count = $allRatings->where('rating', $i)->count();
                                                                                    $percentage = $allRatings->count() > 0 ? ($count / $allRatings->count()) * 100 : 0;
                                                                                ?>
                                                                                <div class="d-flex align-items-center mb-1">
                                                                                    <span style="width: 20px;"><?php echo e($i); ?></span>
                                                                                    <i class="fas fa-star text-warning mx-1"></i>
                                                                                    <div class="progress flex-grow-1 mx-2" style="height: 8px;">
                                                                                        <div class="progress-bar bg-warning" style="width: <?php echo e($percentage); ?>%"></div>
                                                                                    </div>
                                                                                    <span style="width: 40px; font-size: 12px;"><?php echo e($count); ?></span>
                                                                                </div>
                                                                            <?php endfor; ?>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <?php if($allRatings->where('review', '!=', null)->isNotEmpty()): ?>
                                                                        <h6><?php echo e(__('messages.recent_reviews')); ?></h6>
                                                                        <?php $__currentLoopData = $allRatings->where('review', '!=', null)->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rating): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <div class="border-bottom pb-2 mb-2">
                                                                                <div class="d-flex justify-content-between">
                                                                                    <div>
                                                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                                                            <i class="fas fa-star <?php echo e($i <= $rating->rating ? 'text-warning' : 'text-muted'); ?>"></i>
                                                                                        <?php endfor; ?>
                                                                                    </div>
                                                                                    <small class="text-muted"><?php echo e($rating->created_at->format('Y-m-d')); ?></small>
                                                                                </div>
                                                                                <p class="mb-0 mt-1"><?php echo e($rating->review); ?></p>
                                                                            </div>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('messages.close')); ?></button>
                                                <button type="button" class="btn btn-success me-2" onclick="approveDelete(<?php echo e($request->id); ?>)">
                                                    <i class="fas fa-check"></i> <?php echo e(__('messages.approve_deletion')); ?>

                                                </button>
                                                <button type="button" class="btn btn-danger" onclick="rejectDelete(<?php echo e($request->id); ?>)">
                                                    <i class="fas fa-times"></i> <?php echo e(__('messages.reject_deletion')); ?>

                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center"><?php echo e(__('messages.no_delete_requests_found')); ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if(method_exists($deleteRequests, 'links')): ?>
                        <div class="d-flex justify-content-center mt-3">
                            <?php echo e($deleteRequests->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<!-- Add SweetAlert2 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script>
    // Check if Swal is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded!');
    }

    function approveDelete(requestId) {
        Swal.fire({
            title: '<?php echo e(__("messages.are_you_sure")); ?>',
            text: '<?php echo e(__("messages.approve_delete_warning")); ?>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<?php echo e(__("messages.yes_approve")); ?>',
            cancelButtonText: '<?php echo e(__("messages.cancel")); ?>'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo e(route("admin.provider-delete-requests.approve", ":requestId")); ?>'.replace(':requestId', requestId),
                    type: 'POST',
                    data: {
                        _token: '<?php echo e(csrf_token()); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                '<?php echo e(__("messages.approved")); ?>',
                                response.message,
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            '<?php echo e(__("messages.error")); ?>',
                            '<?php echo e(__("messages.something_went_wrong")); ?>',
                            'error'
                        );
                    }
                });
            }
        });
    }

    function rejectDelete(requestId) {
        Swal.fire({
            title: '<?php echo e(__("messages.reject_deletion_request")); ?>',
            input: 'textarea',
            inputLabel: '<?php echo e(__("messages.reason_for_rejection")); ?>',
            inputPlaceholder: '<?php echo e(__("messages.enter_reason")); ?>...',
            inputAttributes: {
                'aria-label': '<?php echo e(__("messages.enter_reason")); ?>'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<?php echo e(__("messages.yes_reject")); ?>',
            cancelButtonText: '<?php echo e(__("messages.cancel")); ?>',
            inputValidator: (value) => {
                if (!value) {
                    return '<?php echo e(__("messages.reason_required")); ?>'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo e(route("admin.provider-delete-requests.reject", ":requestId")); ?>'.replace(':requestId', requestId),
                    type: 'POST',
                    data: {
                        _token: '<?php echo e(csrf_token()); ?>',
                        reason: result.value
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                '<?php echo e(__("messages.rejected")); ?>',
                                response.message,
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            '<?php echo e(__("messages.error")); ?>',
                            '<?php echo e(__("messages.something_went_wrong")); ?>',
                            'error'
                        );
                    }
                });
            }
        });
    }

    // Initialize DataTable if available
    $(document).ready(function() {
        if (typeof $.fn.DataTable !== 'undefined') {
            $('#deleteRequestsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[5, 'desc']], // Sort by request date
                language: {
                    <?php if(app()->getLocale() == 'ar'): ?>
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json'
                    <?php endif; ?>
                }
            });
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/provider-delete-requests/index.blade.php ENDPATH**/ ?>