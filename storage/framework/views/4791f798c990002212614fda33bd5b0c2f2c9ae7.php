<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4><?php echo e(__('messages.Discounts')); ?> - <?php echo e($providerType->name); ?></h4>
                        <small class="text-muted"><?php echo e(__('messages.Provider')); ?>: <?php echo e($providerType->provider->name_of_manager); ?></small>
                    </div>
                    <div class="btn-group">
                        <a href="<?php echo e(route('discounts.create', [$providerId, $providerType->id])); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?php echo e(__('messages.Add_Discount')); ?>

                        </a>
                        <a href="<?php echo e(route('admin.providerDetails.index', $providerId)); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> <?php echo e(__('messages.Back_to_Provider')); ?>

                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if($discounts->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('messages.Name')); ?></th>
                                        <th><?php echo e(__('messages.Discount_Type')); ?></th>
                                        <th><?php echo e(__('messages.Percentage')); ?></th>
                                        <th><?php echo e(__('messages.Date_Range')); ?></th>
                                        <th><?php echo e(__('messages.Services')); ?></th>
                                        <th><?php echo e(__('messages.Status')); ?></th>
                                        <th><?php echo e(__('messages.Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $discounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $discount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="<?php echo e(!$discount->isCurrentlyActive() ? 'table-secondary' : ''); ?>">
                                            <td>
                                                <strong><?php echo e($discount->name); ?></strong>
                                                <?php if($discount->description): ?>
                                                    <br><small class="text-muted"><?php echo e($discount->description); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php echo e(ucfirst($discount->discount_type)); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-success" style="font-size: 14px;">
                                                    <?php echo e($discount->percentage); ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <strong><?php echo e(__('messages.From')); ?>:</strong> <?php echo e($discount->start_date->format('Y-m-d')); ?><br>
                                                    <strong><?php echo e(__('messages.To')); ?>:</strong> <?php echo e($discount->end_date->format('Y-m-d')); ?>

                                                </small>
                                                <?php if($discount->isCurrentlyActive()): ?>
                                                    <br><span class="badge badge-success"><?php echo e(__('messages.Active_Now')); ?></span>
                                                <?php elseif($discount->start_date > now()): ?>
                                                    <br><span class="badge badge-warning"><?php echo e(__('messages.Upcoming')); ?></span>
                                                <?php else: ?>
                                                    <br><span class="badge badge-secondary"><?php echo e(__('messages.Expired')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($discount->services->count() > 0): ?>
                                                    <small>
                                                        <?php $__currentLoopData = $discount->services->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <span class="badge badge-outline-primary"><?php echo e(app()->getLocale() == 'ar' ? $service->name_ar : $service->name_en); ?></span>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if($discount->services->count() > 3): ?>
                                                            <span class="text-muted">+<?php echo e($discount->services->count() - 3); ?> <?php echo e(__('messages.More')); ?></span>
                                                        <?php endif; ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="badge badge-info"><?php echo e(__('messages.All_Services')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($discount->is_active): ?>
                                                    <span class="badge badge-success"><?php echo e(__('messages.Active')); ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger"><?php echo e(__('messages.Inactive')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo e(route('discounts.edit', [$providerId, $providerType->id, $discount->id])); ?>" 
                                                       class="btn btn-outline-primary" title="<?php echo e(__('messages.Edit')); ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <form action="<?php echo e(route('discounts.toggleStatus', [$providerId, $providerType->id, $discount->id])); ?>" 
                                                          method="POST" style="display: inline;">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('PATCH'); ?>
                                                        <button type="submit" class="btn btn-outline-warning" 
                                                                title="<?php echo e($discount->is_active ? __('messages.Deactivate') : __('messages.Activate')); ?>">
                                                            <i class="fas fa-<?php echo e($discount->is_active ? 'pause' : 'play'); ?>"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form action="<?php echo e(route('discounts.destroy', [$providerId, $providerType->id, $discount->id])); ?>" 
                                                          method="POST" style="display: inline;"
                                                          onsubmit="return confirm('<?php echo e(__('messages.Confirm_Delete_Discount')); ?>')">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-outline-danger" 
                                                                title="<?php echo e(__('messages.Delete')); ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-percentage fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted"><?php echo e(__('messages.No_Discounts_Found')); ?></h5>
                            <p class="text-muted"><?php echo e(__('messages.Create_First_Discount_Message')); ?></p>
                            <a href="<?php echo e(route('discounts.create', [$providerId, $providerType->id])); ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> <?php echo e(__('messages.Add_First_Discount')); ?>

                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/discounts/index.blade.php ENDPATH**/ ?>