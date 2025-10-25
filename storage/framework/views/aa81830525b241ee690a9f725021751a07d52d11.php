<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4><?php echo e(__('messages.Provider_Types')); ?>: <?php echo e($provider->name_of_manager); ?></h4>
                        <small class="text-muted"><?php echo e(__('messages.Phone')); ?>: <?php echo e($provider->country_code); ?><?php echo e($provider->phone); ?></small>
                    </div>
                    <div>
                        <a href="<?php echo e(route('admin.providerDetails.create', $provider->id)); ?>" class="btn btn-primary">
                            <?php echo e(__('messages.Add_Type')); ?>

                        </a>
                        <a href="<?php echo e(route('providers.index')); ?>" class="btn btn-secondary">
                            <?php echo e(__('messages.Back_to_Providers')); ?>

                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('messages.Image')); ?></th>
                                    <th><?php echo e(__('messages.Type_Name')); ?></th>
                                    <th><?php echo e(__('messages.Type')); ?></th>
                                    <th><?php echo e(__('messages.Services')); ?></th>
                                    <th><?php echo e(__('messages.Price_Per_Hour')); ?></th>
                                    <th><?php echo e(__('messages.Status')); ?></th>
                                    <th><?php echo e(__('messages.VIP')); ?></th>
                                    <th><?php echo e(__('messages.Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $providerTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $providerType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <?php if($providerType->images->first()): ?>
                                                <img src="<?php echo e($providerType->images->first()->photo_url); ?>" 
                                                     alt="<?php echo e($providerType->name); ?>" 
                                                     class="img-thumbnail" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <small class="text-muted"><?php echo e(__('messages.No_Image')); ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo e($providerType->name); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo e(Str::limit($providerType->description, 50)); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo e(app()->getLocale() == 'ar' ? $providerType->type->name_ar : $providerType->type->name_en); ?>

                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <?php if(isset($providerType->type->booking_type)): ?>
                                                    (<?php echo e($providerType->type->booking_type == 'hourly' ? __('messages.Hourly') : __('messages.Service_Based')); ?>)
                                                <?php else: ?>
                                                    (<?php echo e(__('messages.Hourly')); ?>)
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if(isset($providerType->type->booking_type) && $providerType->type->booking_type == 'service'): ?>
                                                <?php
                                                    $serviceCount = DB::table('provider_services')
                                                        ->where('provider_type_id', $providerType->id)
                                                        ->count();
                                                ?>
                                                <span class="badge bg-success">
                                                    <?php echo e($serviceCount); ?> <?php echo e(__('messages.Services')); ?>

                                                </span>
                                                <?php if($serviceCount > 0): ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo e(__('messages.Service_Based_Pricing')); ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php $__currentLoopData = $providerType->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <span class="badge bg-info me-1 mb-1">
                                                        <?php echo e(app()->getLocale() == 'ar' ? $service->service->name_ar : $service->service->name_en); ?>

                                                    </span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(!isset($providerType->type->booking_type) || $providerType->type->booking_type == 'hourly'): ?>
                                                <?php echo e(number_format($providerType->price_per_hour, 2)); ?> <?php echo e(__('messages.Currency')); ?>

                                            <?php else: ?>
                                                <span class="text-muted"><?php echo e(__('messages.Service_Based_Pricing')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo e($providerType->status == 1 ? 'bg-success' : 'bg-danger'); ?>">
                                                <?php echo e($providerType->status == 1 ? __('messages.On') : __('messages.Off')); ?>

                                            </span>
                                            <br>
                                            <span class="badge <?php echo e($providerType->activate == 1 ? 'bg-success' : 'bg-warning'); ?>">
                                                <?php echo e($providerType->activate == 1 ? __('messages.Active') : __('messages.Inactive')); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo e($providerType->is_vip == 1 ? 'bg-warning text-dark' : 'bg-secondary'); ?>">
                                                <?php echo e($providerType->is_vip == 1 ? __('messages.VIP') : __('messages.Regular')); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm">
                                                <a href="<?php echo e(route('admin.providerDetails.edit', [$provider->id, $providerType->id])); ?>" 
                                                   class="btn btn-warning btn-sm mb-1">
                                                    <?php echo e(__('messages.Edit')); ?>

                                                </a>
                                                
                                                <a href="<?php echo e(route('admin.providerDetails.availabilities', [$provider->id, $providerType->id])); ?>" 
                                                   class="btn btn-info btn-sm mb-1">
                                                    <?php echo e(__('messages.Availability')); ?>

                                                </a>

                                                <a href="<?php echo e(route('discounts.index', [$provider->id, $providerType->id])); ?>" 
                                                    class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-percentage"></i> <?php echo e(__('messages.Manage_Discounts')); ?>

                                                </a>
                                                
                                                <form action="<?php echo e(route('admin.providerDetails.destroy', [$provider->id, $providerType->id])); ?>" 
                                                      method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('<?php echo e(__('messages.Confirm_Delete')); ?>')">
                                                        <?php echo e(__('messages.Delete')); ?>

                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <?php echo e(__('messages.No_Types_Found')); ?>

                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/providerDetails/index.blade.php ENDPATH**/ ?>