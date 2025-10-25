<?php $__env->startSection('title', __('messages.View_provider')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(__('messages.View_provider')); ?></h1>
        <div>
            <a href="<?php echo e(route('providers.edit', $provider->id)); ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> <?php echo e(__('messages.Edit')); ?>

            </a>
            <a href="<?php echo e(route('providers.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> <?php echo e(__('messages.Back_to_List')); ?>

            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <!-- provider Profile -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.Profile')); ?></h6>
                </div>
                <div class="card-body text-center">
                    <?php if($provider->photo): ?>
                    <img src="<?php echo e(asset('assets/admin/uploads/' . $provider->photo_of_manager)); ?>"  class="img-profile rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <?php else: ?>
                    <img src="<?php echo e(asset('assets/admin/img/undraw_profile.svg')); ?>" alt="No Image" class="img-profile rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <?php endif; ?>
                    <h4 class="font-weight-bold"><?php echo e($provider->_of_manager); ?></h4>
                    <p class="text-muted mb-1"><?php echo e($provider->phone); ?></p>
                    <?php if($provider->email): ?>
                    <p class="text-muted mb-1"><?php echo e($provider->email); ?></p>
                    <?php endif; ?>
                    <div class="mt-3">
                        <?php if($provider->activate == 1): ?>
                        <span class="badge badge-success px-3 py-2"><?php echo e(__('messages.Active')); ?></span>
                        <?php else: ?>
                        <span class="badge badge-danger px-3 py-2"><?php echo e(__('messages.Inactive')); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- provider Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.provider_Details')); ?></h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%"><?php echo e(__('messages.ID')); ?></th>
                                    <td><?php echo e($provider->id); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('messages.Name')); ?></th>
                                    <td><?php echo e($provider->name_of_manager); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('messages.Phone')); ?></th>
                                    <td><?php echo e($provider->phone); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('messages.Email')); ?></th>
                                    <td><?php echo e($provider->email ?? __('messages.Not_Available')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('messages.Balance')); ?></th>
                                    <td><?php echo e($provider->balance); ?></td>
                                </tr>
                    
                                <tr>
                                    <th><?php echo e(__('messages.Status')); ?></th>
                                    <td>
                                        <?php if($provider->activate == 1): ?>
                                        <span class="badge badge-success"><?php echo e(__('messages.Active')); ?></span>
                                        <?php else: ?>
                                        <span class="badge badge-danger"><?php echo e(__('messages.Inactive')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('messages.Created_At')); ?></th>
                                    <td><?php echo e($provider->created_at->format('Y-m-d H:i:s')); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('messages.Updated_At')); ?></th>
                                    <td><?php echo e($provider->updated_at->format('Y-m-d H:i:s')); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/providers/show.blade.php ENDPATH**/ ?>