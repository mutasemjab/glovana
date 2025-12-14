

<?php $__env->startSection('title', __('messages.ban_history')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-ban"></i> <?php echo e(__('messages.ban_history')); ?>

        </h1>
        <div>
            <a href="<?php echo e(route('providers.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> <?php echo e(__('messages.back_to_providers')); ?>

            </a>
            <?php if(!$provider->isBanned()): ?>
            <button type="button" class="btn btn-danger" onclick="openBanModal()">
                <i class="fas fa-ban"></i> <?php echo e(__('messages.ban_provider')); ?>

            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Provider Info Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.provider_information')); ?></h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <?php if($provider->photo_of_manager): ?>
                    <img src="<?php echo e(asset('assets/admin/uploads/' . $provider->photo_of_manager)); ?>" 
                         alt="<?php echo e($provider->name_of_manager); ?>" 
                         class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                    <?php else: ?>
                    <img src="<?php echo e(asset('assets/admin/img/no-image.png')); ?>" 
                         alt="No Image" 
                         class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                    <?php endif; ?>
                </div>
                <div class="col-md-10">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong><?php echo e(__('messages.ID')); ?>:</strong> #<?php echo e($provider->id); ?></p>
                            <p><strong><?php echo e(__('messages.Name')); ?>:</strong> <?php echo e($provider->name_of_manager); ?></p>
                            <p><strong><?php echo e(__('messages.Phone')); ?>:</strong> <?php echo e($provider->country_code); ?> <?php echo e($provider->phone); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><?php echo e(__('messages.Email')); ?>:</strong> <?php echo e($provider->email ?? __('messages.not_available')); ?></p>
                            <p><strong><?php echo e(__('messages.Balance')); ?>:</strong> 
                                <span class="badge <?php echo e($provider->balance > 0 ? 'badge-success' : ($provider->balance < 0 ? 'badge-danger' : 'badge-warning')); ?>">
                                    <?php echo e(number_format($provider->balance, 2)); ?> JD
                                </span>
                            </p>
                            <p><strong><?php echo e(__('messages.Status')); ?>:</strong>
                                <?php if($provider->isBanned()): ?>
                                    <span class="badge badge-danger"><?php echo e(__('messages.banned')); ?></span>
                                <?php elseif($provider->activate == 1): ?>
                                    <span class="badge badge-success"><?php echo e(__('messages.Active')); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><?php echo e(__('messages.Inactive')); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Ban Alert -->
    <?php if($provider->activeBan): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5><i class="fas fa-exclamation-triangle"></i> <?php echo e(__('messages.active_ban')); ?></h5>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <p><strong><?php echo e(__('messages.ban_reason')); ?>:</strong> <?php echo e($provider->activeBan->getReasonText(app()->getLocale())); ?></p>
                <p><strong><?php echo e(__('messages.ban_type')); ?>:</strong> 
                    <span class="badge badge-<?php echo e($provider->activeBan->is_permanent ? 'danger' : 'warning'); ?>">
                        <?php echo e($provider->activeBan->is_permanent ? __('messages.permanent') : __('messages.temporary')); ?>

                    </span>
                </p>
                <?php if(!$provider->activeBan->is_permanent): ?>
                <p><strong><?php echo e(__('messages.expires_at')); ?>:</strong> <?php echo e($provider->activeBan->ban_until->format('Y-m-d H:i')); ?></p>
                <p><strong><?php echo e(__('messages.remaining_time')); ?>:</strong> <?php echo e($provider->activeBan->getRemainingTime(app()->getLocale())); ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <p><strong><?php echo e(__('messages.banned_by')); ?>:</strong> <?php echo e($provider->activeBan->admin->name ?? __('messages.system')); ?></p>
                <p><strong><?php echo e(__('messages.banned_at')); ?>:</strong> <?php echo e($provider->activeBan->banned_at->format('Y-m-d H:i')); ?></p>
                <?php if($provider->activeBan->ban_description): ?>
                <p><strong><?php echo e(__('messages.description')); ?>:</strong><br><?php echo e($provider->activeBan->ban_description); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-3">
            <button type="button" class="btn btn-success" onclick="openUnbanModal(<?php echo e($provider->activeBan->id); ?>)">
                <i class="fas fa-unlock"></i> <?php echo e(__('messages.unban_provider')); ?>

            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Ban History Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <?php echo e(__('messages.ban_history_records')); ?>

                <span class="badge badge-info"><?php echo e($provider->bans->count()); ?></span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?php echo e(__('messages.ID')); ?></th>
                            <th><?php echo e(__('messages.ban_reason')); ?></th>
                            <th><?php echo e(__('messages.ban_type')); ?></th>
                            <th><?php echo e(__('messages.banned_at')); ?></th>
                            <th><?php echo e(__('messages.ban_until')); ?></th>
                            <th><?php echo e(__('messages.banned_by')); ?></th>
                            <th><?php echo e(__('messages.Status')); ?></th>
                            <th><?php echo e(__('messages.Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $provider->bans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ban): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="<?php echo e($ban->is_active ? 'table-danger' : ''); ?>">
                            <td><?php echo e($ban->id); ?></td>
                            <td>
                                <strong><?php echo e($ban->getReasonText(app()->getLocale())); ?></strong>
                                <?php if($ban->ban_description): ?>
                                <br><small class="text-muted"><?php echo e(Str::limit($ban->ban_description, 50)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo e($ban->is_permanent ? 'danger' : 'warning'); ?>">
                                    <?php echo e($ban->is_permanent ? __('messages.permanent') : __('messages.temporary')); ?>

                                </span>
                            </td>
                            <td><?php echo e($ban->banned_at->format('Y-m-d H:i')); ?></td>
                            <td>
                                <?php if($ban->is_permanent): ?>
                                    <span class="text-danger"><?php echo e(__('messages.permanent')); ?></span>
                                <?php elseif($ban->ban_until): ?>
                                    <?php echo e($ban->ban_until->format('Y-m-d H:i')); ?>

                                    <br><small class="text-muted"><?php echo e($ban->getRemainingTime(app()->getLocale())); ?></small>
                                <?php else: ?>
                                    <span class="text-muted"><?php echo e(__('messages.not_available')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($ban->admin->name ?? __('messages.system')); ?></td>
                            <td>
                                <span class="badge badge-<?php echo e($ban->is_active ? 'danger' : 'success'); ?>">
                                    <?php echo e($ban->getStatusText(app()->getLocale())); ?>

                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm" onclick="viewBanDetails(<?php echo e($ban->id); ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if($ban->is_active): ?>
                                <button type="button" class="btn btn-success btn-sm" onclick="openUnbanModal(<?php echo e($ban->id); ?>)">
                                    <i class="fas fa-unlock"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center"><?php echo e(__('messages.no_ban_records')); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Ban Provider Modal -->
<div class="modal fade" id="banModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-ban"></i> <?php echo e(__('messages.ban_provider')); ?>

                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="<?php echo e(route('providers.ban', $provider->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo e(__('messages.ban_warning')); ?>

                    </div>

                    <div class="form-group">
                        <label><?php echo e(__('messages.ban_reason')); ?> <span class="text-danger">*</span></label>
                        <select name="ban_reason" class="form-control" required>
                            <option value=""><?php echo e(__('messages.select_reason')); ?></option>
                            <?php $__currentLoopData = \App\Models\ProviderBan::BAN_REASONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($reason[app()->getLocale()]); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><?php echo e(__('messages.ban_description')); ?></label>
                        <textarea name="ban_description" class="form-control" rows="3" 
                                  placeholder="<?php echo e(__('messages.ban_description_placeholder')); ?>"></textarea>
                    </div>

                    <div class="form-group">
                        <label><?php echo e(__('messages.ban_type')); ?> <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ban_type" 
                                       id="temporary" value="temporary" checked onchange="toggleDuration()">
                                <label class="form-check-label" for="temporary">
                                    <?php echo e(__('messages.temporary')); ?>

                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ban_type" 
                                       id="permanent" value="permanent" onchange="toggleDuration()">
                                <label class="form-check-label" for="permanent">
                                    <?php echo e(__('messages.permanent')); ?>

                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="durationFields">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?php echo e(__('messages.ban_duration')); ?></label>
                                    <input type="number" name="ban_duration" class="form-control" 
                                           min="1" value="7" placeholder="7">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?php echo e(__('messages.duration_unit')); ?></label>
                                    <select name="ban_duration_unit" class="form-control">
                                        <option value="hours"><?php echo e(__('messages.hours')); ?></option>
                                        <option value="days" selected><?php echo e(__('messages.days')); ?></option>
                                        <option value="weeks"><?php echo e(__('messages.weeks')); ?></option>
                                        <option value="months"><?php echo e(__('messages.months')); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <?php echo e(__('messages.cancel')); ?>

                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban"></i> <?php echo e(__('messages.ban_provider')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Unban Modal -->
<div class="modal fade" id="unbanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-unlock"></i> <?php echo e(__('messages.unban_provider')); ?>

                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="unbanForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <?php echo e(__('messages.unban_info')); ?>

                    </div>

                    <div class="form-group">
                        <label><?php echo e(__('messages.unban_reason')); ?> <span class="text-danger">*</span></label>
                        <textarea name="unban_reason" class="form-control" rows="3" required
                                  placeholder="<?php echo e(__('messages.unban_reason_placeholder')); ?>"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <?php echo e(__('messages.cancel')); ?>

                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-unlock"></i> <?php echo e(__('messages.unban_provider')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ban Details Modal -->
<div class="modal fade" id="banDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> <?php echo e(__('messages.ban_details')); ?>

                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="banDetailsContent">
                <!-- Content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <?php echo e(__('messages.close')); ?>

                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
function openBanModal() {
    $('#banModal').modal('show');
}

function toggleDuration() {
    const isPermanent = $('input[name="ban_type"]:checked').val() === 'permanent';
    $('#durationFields').toggle(!isPermanent);
    
    if (isPermanent) {
        $('input[name="ban_duration"]').removeAttr('required');
        $('select[name="ban_duration_unit"]').removeAttr('required');
    } else {
        $('input[name="ban_duration"]').attr('required', 'required');
        $('select[name="ban_duration_unit"]').attr('required', 'required');
    }
}

function openUnbanModal(banId) {
    const formAction = '<?php echo e(route("providers.unban", [$provider->id, "BAN_ID"])); ?>'.replace('BAN_ID', banId);
    $('#unbanForm').attr('action', formAction);
    $('#unbanModal').modal('show');
}

function viewBanDetails(banId) {
    // Find ban data from the table
    const ban = <?php echo json_encode($provider->bans, 15, 512) ?>;
    const banData = ban.find(b => b.id === banId);
    
    if (banData) {
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <p><strong><?php echo e(__('messages.ban_reason')); ?>:</strong> ${banData.ban_reason}</p>
                    <p><strong><?php echo e(__('messages.ban_type')); ?>:</strong> 
                        <span class="badge badge-${banData.is_permanent ? 'danger' : 'warning'}">
                            ${banData.is_permanent ? '<?php echo e(__("messages.permanent")); ?>' : '<?php echo e(__("messages.temporary")); ?>'}
                        </span>
                    </p>
                    <p><strong><?php echo e(__('messages.banned_at')); ?>:</strong> ${new Date(banData.banned_at).toLocaleString()}</p>
                    ${!banData.is_permanent && banData.ban_until ? `<p><strong><?php echo e(__('messages.ban_until')); ?>:</strong> ${new Date(banData.ban_until).toLocaleString()}</p>` : ''}
                </div>
                <div class="col-md-6">
                    <p><strong><?php echo e(__('messages.Status')); ?>:</strong> 
                        <span class="badge badge-${banData.is_active ? 'danger' : 'success'}">
                            ${banData.is_active ? '<?php echo e(__("messages.Active")); ?>' : '<?php echo e(__("messages.Lifted")); ?>'}
                        </span>
                    </p>
                    ${banData.unbanned_at ? `<p><strong><?php echo e(__('messages.unbanned_at')); ?>:</strong> ${new Date(banData.unbanned_at).toLocaleString()}</p>` : ''}
                </div>
            </div>
            ${banData.ban_description ? `<hr><p><strong><?php echo e(__('messages.description')); ?>:</strong><br>${banData.ban_description}</p>` : ''}
            ${banData.unban_reason ? `<hr><p><strong><?php echo e(__('messages.unban_reason')); ?>:</strong><br>${banData.unban_reason}</p>` : ''}
        `;
        
        $('#banDetailsContent').html(html);
        $('#banDetailsModal').modal('show');
    }
}

$(document).ready(function() {
    toggleDuration(); // Initialize on page load
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/providers/bans/history.blade.php ENDPATH**/ ?>