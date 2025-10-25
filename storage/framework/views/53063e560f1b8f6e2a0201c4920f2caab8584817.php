


<?php $__env->startSection('css'); ?>
<style>
       .property-card {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        .property-header {
            background-color: #f8f9fa;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
        }
        .property-content {
            padding: 1rem;
        }
        .old-value {
            background-color: #f8d7da;
            color: #721c24;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-family: monospace;
        }
        .new-value {
            background-color: #d1edff;
            color: #0c5460;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-family: monospace;
        }
        .json-content {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
        .badge-created { background-color: #198754; }
        .badge-updated { background-color: #fd7e14; }
        .badge-deleted { background-color: #dc3545; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Activity Log Details</h1>
        <a href="<?php echo e(route('admin.activity-logs.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Activity Logs
        </a>
    </div>

    <!-- Activity Overview Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Activity Overview</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Event:</strong></td>
                            <td>
                                <span class="badge badge-<?php echo e($activity->event); ?>">
                                    <?php echo e(ucfirst($activity->event)); ?>

                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Model:</strong></td>
                            <td><?php echo e(class_basename($activity->subject_type)); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Model ID:</strong></td>
                            <td><?php echo e($activity->subject_id ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Log Name:</strong></td>
                            <td><?php echo e($activity->log_name); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Performed By:</strong></td>
                            <td>
                                <?php if($activity->causer): ?>
                                    <?php echo e($activity->causer->name ?? $activity->causer->username); ?>

                                    <small class="text-muted">(<?php echo e(class_basename($activity->causer_type)); ?>)</small>
                                <?php else: ?>
                                    <span class="text-muted">System</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Date & Time:</strong></td>
                            <td><?php echo e($activity->created_at->format('Y-m-d H:i:s')); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Description:</strong></td>
                            <td><?php echo e($activity->description ?? 'No description'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>IP Address:</strong></td>
                            <td><?php echo e($activity->properties['ip'] ?? 'N/A'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Changes Details -->
    <?php if($activity->properties->isNotEmpty()): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Changes Details</h5>
        </div>
        <div class="card-body">
            <?php if($activity->event === 'updated' && $activity->properties->has('old') && $activity->properties->has('attributes')): ?>
                <!-- Updated Records - Show Old vs New -->
                <h6>Field Changes:</h6>
                <?php
                    $oldValues = $activity->properties['old'] ?? [];
                    $newValues = $activity->properties['attributes'] ?? [];
                ?>
                
                <?php $__currentLoopData = $newValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $newValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="property-card">
                        <div class="property-header">
                            <strong><?php echo e(ucfirst(str_replace('_', ' ', $field))); ?></strong>
                        </div>
                        <div class="property-content">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Old Value:</small><br>
                                    <span class="old-value">
                                        <?php echo e($oldValues[$field] ?? 'N/A'); ?>

                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">New Value:</small><br>
                                    <span class="new-value">
                                        <?php echo e($newValue); ?>

                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php elseif($activity->event === 'created' && $activity->properties->has('attributes')): ?>
                <!-- Created Records - Show All Attributes -->
                <h6>Created With Attributes:</h6>
                <?php $__currentLoopData = $activity->properties['attributes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="property-card">
                        <div class="property-header">
                            <strong><?php echo e(ucfirst(str_replace('_', ' ', $field))); ?></strong>
                        </div>
                        <div class="property-content">
                            <span class="new-value"><?php echo e($value); ?></span>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php elseif($activity->event === 'deleted' && $activity->properties->has('old')): ?>
                <!-- Deleted Records - Show What Was Deleted -->
                <h6>Deleted Record Attributes:</h6>
                <?php $__currentLoopData = $activity->properties['old']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="property-card">
                        <div class="property-header">
                            <strong><?php echo e(ucfirst(str_replace('_', ' ', $field))); ?></strong>
                        </div>
                        <div class="property-content">
                            <span class="old-value"><?php echo e($value); ?></span>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

            <!-- Custom Properties -->
            <?php
                $customProperties = $activity->properties->except(['old', 'attributes']);
            ?>
            
            <?php if($customProperties->isNotEmpty()): ?>
                <h6 class="mt-4">Additional Properties:</h6>
                <?php $__currentLoopData = $customProperties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="property-card">
                        <div class="property-header">
                            <strong><?php echo e(ucfirst(str_replace('_', ' ', $key))); ?></strong>
                        </div>
                        <div class="property-content">
                            <?php if(is_array($value) || is_object($value)): ?>
                                <div class="json-content"><?php echo e(json_encode($value, JSON_PRETTY_PRINT)); ?></div>
                            <?php else: ?>
                                <?php echo e($value); ?>

                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Raw Data (Optional - for debugging) -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#rawData">
                    Raw Activity Data (JSON)
                </button>
            </h5>
        </div>
        <div id="rawData" class="collapse">
            <div class="card-body">
                <div class="json-content"><?php echo e(json_encode($activity->toArray(), JSON_PRETTY_PRINT)); ?></div>
            </div>
        </div>
    </div>

    <!-- Related Record (if exists) -->
    <?php if($activity->subject): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Related Record</h5>
        </div>
        <div class="card-body">
            <p><strong>Type:</strong> <?php echo e(class_basename($activity->subject_type)); ?></p>
            <p><strong>ID:</strong> <?php echo e($activity->subject_id); ?></p>
            <?php if(method_exists($activity->subject, 'name')): ?>
                <p><strong>Name:</strong> <?php echo e($activity->subject->name); ?></p>
            <?php elseif(method_exists($activity->subject, 'title')): ?>
                <p><strong>Title:</strong> <?php echo e($activity->subject->title); ?></p>
            <?php endif; ?>
            <!-- Add a link to view the actual record if needed -->
            <!-- <a href="<?php echo e(route('admin.{model}.show', $activity->subject_id)); ?>" class="btn btn-primary btn-sm">View Record</a> -->
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/activity-logs/show.blade.php ENDPATH**/ ?>