




<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Activity Logs</h1>
    
    <!-- Filters -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <select name="model" class="form-control">
                    <option value="">All Models</option>
                    <option value="App\Models\User" <?php echo e(request('model') == 'App\Models\User' ? 'selected' : ''); ?>>Users</option>
                    <option value="App\Models\Admin" <?php echo e(request('model') == 'App\Models\Admin' ? 'selected' : ''); ?>>Admins</option>
                    <!-- Add other models -->
                </select>
            </div>
            <div class="col-md-3">
                <select name="event" class="form-control">
                    <option value="">All Events</option>
                    <option value="created" <?php echo e(request('event') == 'created' ? 'selected' : ''); ?>>Created</option>
                    <option value="updated" <?php echo e(request('event') == 'updated' ? 'selected' : ''); ?>>Updated</option>
                    <option value="deleted" <?php echo e(request('event') == 'deleted' ? 'selected' : ''); ?>>Deleted</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Activity Table -->
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Event</th>
                <th>Model</th>
                <th>User</th>
                <th>Changes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($activity->created_at->format('Y-m-d H:i:s')); ?></td>
                <td>
                    <span class="badge badge-<?php echo e($activity->event == 'created' ? 'success' : ($activity->event == 'updated' ? 'warning' : 'danger')); ?>">
                        <?php echo e(ucfirst($activity->event)); ?>

                    </span>
                </td>
                <td><?php echo e(class_basename($activity->subject_type)); ?></td>
                <td>
                    <?php if($activity->causer): ?>
                        <?php echo e($activity->causer->name ?? $activity->causer->username); ?>

                    <?php else: ?>
                        System
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($activity->properties->has('attributes')): ?>
                        <?php echo e(count($activity->properties['attributes'])); ?> fields changed
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?php echo e(route('admin.activity-logs.show', $activity->id)); ?>" class="btn btn-sm btn-info">View</a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <?php echo e($activities->links()); ?>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/activity-logs/index.blade.php ENDPATH**/ ?>