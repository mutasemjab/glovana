<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h4><?php echo e(__('messages.Add_Type_for_Provider')); ?>: <?php echo e($provider->name_of_manager); ?></h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.providerDetails.store', $provider->id)); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        
                        <div class="row">
                            <!-- Type Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3"><?php echo e(__('messages.Type_Information')); ?></h5>
                                
                                <div class="mb-3">
                                    <label for="type_id" class="form-label"><?php echo e(__('messages.Type')); ?></label>
                                    <select class="form-control <?php $__errorArgs = ['type_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                            id="type_id" name="type_id" required onchange="handleTypeChange()">
                                        <option value=""><?php echo e(__('messages.Select_Type')); ?></option>
                                        <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($type->id); ?>" 
                                                    data-booking-type="<?php echo e($type->booking_type ?? 'hourly'); ?>"
                                                    <?php echo e(old('type_id') == $type->id ? 'selected' : ''); ?>>
                                                <?php echo e(app()->getLocale() == 'ar' ? $type->name_ar : $type->name_en); ?>

                                                <?php if(isset($type->booking_type)): ?>
                                                    (<?php echo e($type->booking_type == 'hourly' ? __('messages.Hourly') : __('messages.Service_Based')); ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['type_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <!-- Hourly Services (checkbox style) -->
                                <div class="mb-3" id="hourly-services" style="display: none;">
                                    <label for="service_ids" class="form-label"><?php echo e(__('messages.Services')); ?></label>
                                    <div class="border p-3 rounded <?php $__errorArgs = ['service_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" style="max-height: 200px; overflow-y: auto;">
                                        <div class="mb-2 border-bottom pb-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="selectAllServices()">
                                                <?php echo e(__('messages.Select_All')); ?>

                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllServices()">
                                                <?php echo e(__('messages.Deselect_All')); ?>

                                            </button>
                                        </div>
                                        <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="form-check">
                                                <input class="form-check-input hourly-service-checkbox" type="checkbox" 
                                                       name="service_ids[]" value="<?php echo e($service->id); ?>" 
                                                       id="service_<?php echo e($service->id); ?>" 
                                                       <?php echo e(in_array($service->id, old('service_ids', [])) ? 'checked' : ''); ?>>
                                                <label class="form-check-label" for="service_<?php echo e($service->id); ?>">
                                                    <?php echo e(app()->getLocale() == 'ar' ? $service->name_ar : $service->name_en); ?>

                                                </label>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <div class="form-text"><?php echo e(__('messages.Select_Multiple_Services')); ?></div>
                                    <?php $__errorArgs = ['service_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <!-- Service-based Services (with pricing) -->
                                <div class="mb-3" id="service-based-services" style="display: none;">
                                    <label class="form-label"><?php echo e(__('messages.Services_with_Pricing')); ?></label>
                                    <div class="border p-3 rounded <?php $__errorArgs = ['service_prices'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" style="max-height: 300px; overflow-y: auto;">
                                        <div class="mb-2 text-info">
                                            <small><i class="fas fa-info-circle"></i> <?php echo e(__('messages.Enter_Price_For_Each_Service')); ?></small>
                                        </div>
                                        <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="row mb-2 align-items-center border-bottom pb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label mb-0 fw-bold">
                                                        <?php echo e(app()->getLocale() == 'ar' ? $service->name_ar : $service->name_en); ?>

                                                    </label>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" 
                                                               class="form-control service-price-input" 
                                                               name="service_prices[<?php echo e($service->id); ?>]" 
                                                               placeholder="<?php echo e(__('messages.Price')); ?>"
                                                               value="<?php echo e(old('service_prices.'.$service->id)); ?>"
                                                               step="0.01" 
                                                               min="0">
                                                        <span class="input-group-text"><?php echo e(__('messages.Currency')); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <div class="form-text"><?php echo e(__('messages.Leave_Empty_To_Exclude_Service')); ?></div>
                                    <?php $__errorArgs = ['service_prices'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label"><?php echo e(__('messages.Type_Name')); ?></label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="name" name="name" value="<?php echo e(old('name')); ?>" required>
                                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label"><?php echo e(__('messages.Description')); ?></label>
                                    <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                              id="description" name="description" rows="4" required><?php echo e(old('description')); ?></textarea>
                                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3" id="hourly-price-field" style="display: none;">
                                    <label for="price_per_hour" class="form-label"><?php echo e(__('messages.Price_Per_Hour')); ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control <?php $__errorArgs = ['price_per_hour'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                               id="price_per_hour" name="price_per_hour" value="<?php echo e(old('price_per_hour')); ?>" 
                                               step="0.01" min="0">
                                        <span class="input-group-text"><?php echo e(__('messages.Currency')); ?></span>
                                    </div>
                                    <?php $__errorArgs = ['price_per_hour'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <!-- Location & Settings -->
                            <div class="col-md-6">
                                <h5 class="mb-3"><?php echo e(__('messages.Location_Settings')); ?></h5>
                                
                                <div class="mb-3">
                                    <label for="lat" class="form-label"><?php echo e(__('messages.Latitude')); ?></label>
                                    <input type="number" class="form-control <?php $__errorArgs = ['lat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="lat" name="lat" value="<?php echo e(old('lat')); ?>" step="any" required>
                                    <?php $__errorArgs = ['lat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="lng" class="form-label"><?php echo e(__('messages.Longitude')); ?></label>
                                    <input type="number" class="form-control <?php $__errorArgs = ['lng'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="lng" name="lng" value="<?php echo e(old('lng')); ?>" step="any" required>
                                    <?php $__errorArgs = ['lng'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label"><?php echo e(__('messages.Address')); ?></label>
                                    <textarea class="form-control <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                              id="address" name="address" rows="3"><?php echo e(old('address')); ?></textarea>
                                    <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="activate" class="form-label"><?php echo e(__('messages.Activate')); ?></label>
                                    <select class="form-control <?php $__errorArgs = ['activate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                            id="activate" name="activate" required>
                                        <option value="1" <?php echo e(old('activate') == '1' ? 'selected' : ''); ?>><?php echo e(__('messages.Active')); ?></option>
                                        <option value="2" <?php echo e(old('activate') == '2' ? 'selected' : ''); ?>><?php echo e(__('messages.Inactive')); ?></option>
                                    </select>
                                    <?php $__errorArgs = ['activate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label"><?php echo e(__('messages.Status')); ?></label>
                                    <select class="form-control <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                            id="status" name="status" required>
                                        <option value="1" <?php echo e(old('status') == '1' ? 'selected' : ''); ?>><?php echo e(__('messages.On')); ?></option>
                                        <option value="2" <?php echo e(old('status') == '2' ? 'selected' : ''); ?>><?php echo e(__('messages.Off')); ?></option>
                                    </select>
                                    <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="is_vip" class="form-label"><?php echo e(__('messages.VIP_Status')); ?></label>
                                    <select class="form-control <?php $__errorArgs = ['is_vip'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                            id="is_vip" name="is_vip" required>
                                        <option value="1" <?php echo e(old('is_vip') == '1' ? 'selected' : ''); ?>><?php echo e(__('messages.VIP')); ?></option>
                                        <option value="2" <?php echo e(old('is_vip') == '2' ? 'selected' : ''); ?>><?php echo e(__('messages.Regular')); ?></option>
                                    </select>
                                    <?php $__errorArgs = ['is_vip'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="practice_license" class="form-label"><?php echo e(__('messages.Practice_License_Image')); ?></label>
                            <input type="file" class="form-control <?php $__errorArgs = ['practice_license'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                id="practice_license" name="practice_license" accept="image/*">
                            <?php $__errorArgs = ['practice_license'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="identity_photo" class="form-label"><?php echo e(__('messages.Identity_Photo_Image')); ?></label>
                            <input type="file" class="form-control <?php $__errorArgs = ['identity_photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                id="identity_photo" name="identity_photo" accept="image/*">
                            <?php $__errorArgs = ['identity_photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>


                        <!-- Images Section -->
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3"><?php echo e(__('messages.Type_Images')); ?></h5>
                                
                                <div class="mb-3">
                                    <label for="images" class="form-label"><?php echo e(__('messages.Upload_Type_Images')); ?></label>
                                    <input type="file" class="form-control <?php $__errorArgs = ['images.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="images" name="images[]" multiple accept="image/*">
                                    <div class="form-text"><?php echo e(__('messages.Multiple_Images_Allowed')); ?></div>
                                    <?php $__errorArgs = ['images.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3"><?php echo e(__('messages.Gallery_Images')); ?></h5>
                                
                                <div class="mb-3">
                                    <label for="galleries" class="form-label"><?php echo e(__('messages.Upload_Gallery_Images')); ?></label>
                                    <input type="file" class="form-control <?php $__errorArgs = ['galleries.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="galleries" name="galleries[]" multiple accept="image/*">
                                    <div class="form-text"><?php echo e(__('messages.Multiple_Images_Allowed')); ?></div>
                                    <?php $__errorArgs = ['galleries.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><?php echo e(__('messages.Save')); ?></button>
                            <a href="<?php echo e(route('admin.providerDetails.index', $provider->id)); ?>" class="btn btn-secondary"><?php echo e(__('messages.Cancel')); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function handleTypeChange() {
    const typeSelect = document.getElementById('type_id');
    const selectedOption = typeSelect.options[typeSelect.selectedIndex];
    const bookingType = selectedOption.dataset.bookingType || 'hourly';
    
    const hourlyServices = document.getElementById('hourly-services');
    const serviceBasedServices = document.getElementById('service-based-services');
    const hourlyPriceField = document.getElementById('hourly-price-field');
    
    if (bookingType === 'hourly') {
        // Show hourly booking elements
        hourlyServices.style.display = 'block';
        hourlyPriceField.style.display = 'block';
        serviceBasedServices.style.display = 'none';
        
        // Make hourly price required
        document.getElementById('price_per_hour').required = true;
        
        // Clear service-based inputs
        document.querySelectorAll('.service-price-input').forEach(input => {
            input.value = '';
            input.required = false;
        });
        
    } else if (bookingType === 'service') {
        // Show service-based booking elements
        serviceBasedServices.style.display = 'block';
        hourlyServices.style.display = 'none';
        hourlyPriceField.style.display = 'none';
        
        // Make hourly price not required
        document.getElementById('price_per_hour').required = false;
        document.getElementById('price_per_hour').value = '';
        
        // Clear hourly service checkboxes
        document.querySelectorAll('.hourly-service-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
    } else {
        // Hide all service-related elements
        hourlyServices.style.display = 'none';
        serviceBasedServices.style.display = 'none';
        hourlyPriceField.style.display = 'none';
        
        // Clear all inputs
        document.getElementById('price_per_hour').required = false;
        document.getElementById('price_per_hour').value = '';
        document.querySelectorAll('.service-price-input').forEach(input => {
            input.value = '';
            input.required = false;
        });
        document.querySelectorAll('.hourly-service-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
    }
}

function selectAllServices() {
    const checkboxes = document.querySelectorAll('input[name="service_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function deselectAllServices() {
    const checkboxes = document.querySelectorAll('input[name="service_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    handleTypeChange();
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/providerDetails/create.blade.php ENDPATH**/ ?>