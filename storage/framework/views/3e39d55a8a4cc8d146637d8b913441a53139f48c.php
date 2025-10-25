<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h4><?php echo e(__('messages.Create_Discount')); ?></h4>
                    <small class="text-muted"><?php echo e(__('messages.Provider_Type')); ?>: <?php echo e($providerType->name); ?> | <?php echo e(__('messages.Provider')); ?>: <?php echo e($providerType->provider->name_of_manager); ?></small>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('discounts.store', [$providerId, $providerType->id])); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3"><?php echo e(__('messages.Basic_Information')); ?></h5>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label"><?php echo e(__('messages.Discount_Name')); ?> <span class="text-danger">*</span></label>
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
                                              id="description" name="description" rows="3"><?php echo e(old('description')); ?></textarea>
                                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="percentage" class="form-label"><?php echo e(__('messages.Discount_Percentage')); ?> <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control <?php $__errorArgs = ['percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                               id="percentage" name="percentage" value="<?php echo e(old('percentage')); ?>" 
                                               min="0" max="100" step="0.01" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <?php $__errorArgs = ['percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <div class="form-text"><?php echo e(__('messages.Enter_Percentage_0_to_100')); ?></div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label"><?php echo e(__('messages.Start_Date')); ?> <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                   id="start_date" name="start_date" value="<?php echo e(old('start_date', date('Y-m-d'))); ?>" required>
                                            <?php $__errorArgs = ['start_date'];
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
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label"><?php echo e(__('messages.End_Date')); ?> <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                   id="end_date" name="end_date" value="<?php echo e(old('end_date')); ?>" required>
                                            <?php $__errorArgs = ['end_date'];
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
                            </div>

                            <!-- Discount Settings -->
                            <div class="col-md-6">
                                <h5 class="mb-3"><?php echo e(__('messages.Discount_Settings')); ?></h5>
                                
                                <!-- Hidden input for discount type (automatically determined) -->
                                <input type="hidden" id="discount_type" name="discount_type" value="<?php echo e(old('discount_type', $providerType->type->booking_type)); ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo e(__('messages.Discount_Type')); ?></label>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong><?php echo e(__('messages.Auto_Determined_Type')); ?>:</strong>
                                        <?php if($providerType->type->booking_type == 'hourly'): ?>
                                            <span class="badge badge-primary"><?php echo e(__('messages.Hourly_Pricing_Only')); ?></span>
                                            <br><small><?php echo e(__('messages.Applied_to_hourly_rate')); ?></small>
                                        <?php elseif($providerType->type->booking_type == 'service'): ?>
                                            <span class="badge badge-success"><?php echo e(__('messages.Service_Pricing_Only')); ?></span>
                                            <br><small><?php echo e(__('messages.Applied_to_individual_services')); ?></small>
                                        <?php else: ?>
                                            <span class="badge badge-warning"><?php echo e(__('messages.Both_Hourly_and_Service')); ?></span>
                                            <br><small><?php echo e(__('messages.Applied_to_both_pricing_types')); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-text">
                                        <?php echo e(__('messages.Discount_type_automatically_determined')); ?>

                                    </div>
                                </div>

                                <!-- Service Selection -->
                                <div class="mb-3" id="service-selection" style="<?php echo e($providerType->type->booking_type == 'service' ? 'display: block;' : 'display: none;'); ?>">
                                    <label class="form-label"><?php echo e(__('messages.Apply_to_Services')); ?></label>
                                    <div class="border p-3 rounded <?php $__errorArgs = ['service_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" style="max-height: 250px; overflow-y: auto;">
                                        <div class="mb-2 border-bottom pb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="all_services" onchange="toggleAllServices()">
                                                <label class="form-check-label fw-bold" for="all_services">
                                                    <?php echo e(__('messages.Apply_to_All_Services')); ?>

                                                </label>
                                            </div>
                                            <div class="form-text"><?php echo e(__('messages.Leave_unchecked_for_specific_services')); ?></div>
                                        </div>
                                        
                                        <div id="specific-services">
                                            <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="form-check">
                                                    <input class="form-check-input service-checkbox" type="checkbox" 
                                                           name="service_ids[]" value="<?php echo e($service->id); ?>" 
                                                           id="service_<?php echo e($service->id); ?>" 
                                                           <?php echo e(in_array($service->id, old('service_ids', [])) ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="service_<?php echo e($service->id); ?>">
                                                        <?php echo e(app()->getLocale() == 'ar' ? $service->name_ar : $service->name_en); ?>

                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                    <?php $__errorArgs = ['service_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <div class="form-text">
                                        <?php echo e(__('messages.If_no_services_selected_applies_to_all')); ?>

                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="is_active" class="form-label"><?php echo e(__('messages.Status')); ?> <span class="text-danger">*</span></label>
                                    <select class="form-control <?php $__errorArgs = ['is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                            id="is_active" name="is_active" required>
                                        <option value="1" <?php echo e(old('is_active', '1') == '1' ? 'selected' : ''); ?>>
                                            <?php echo e(__('messages.Active')); ?>

                                        </option>
                                        <option value="0" <?php echo e(old('is_active') == '0' ? 'selected' : ''); ?>>
                                            <?php echo e(__('messages.Inactive')); ?>

                                        </option>
                                    </select>
                                    <?php $__errorArgs = ['is_active'];
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

                        <!-- Preview Section -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> <?php echo e(__('messages.Discount_Preview')); ?></h6>
                                    <div id="discount-preview">
                                        <?php echo e(__('messages.Select_discount_type_to_see_preview')); ?>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"><?php echo e(__('messages.Create_Discount')); ?></button>
                                <a href="<?php echo e(route('discounts.index', [$providerId, $providerType->id])); ?>" class="btn btn-secondary"><?php echo e(__('messages.Cancel')); ?></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize based on provider type booking_type
const providerBookingType = '<?php echo e($providerType->type->booking_type); ?>';

function handleDiscountTypeChange() {
    // This function is now simplified since type is auto-determined
    updatePreview();
}

function toggleAllServices() {
    const allServicesCheckbox = document.getElementById('all_services');
    const specificServices = document.getElementById('specific-services');
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    
    if (allServicesCheckbox.checked) {
        specificServices.style.display = 'none';
        serviceCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    } else {
        specificServices.style.display = 'block';
    }
    
    updatePreview();
}

function updatePreview() {
    const discountType = providerBookingType; // Use provider type's booking_type
    const percentage = document.getElementById('percentage').value;
    const serviceSelection = document.getElementById('service-selection');
    const previewDiv = document.getElementById('discount-preview');
    
    // Show/hide service selection based on provider type
    if (discountType === 'service') {
        serviceSelection.style.display = 'block';
    } else {
        serviceSelection.style.display = 'none';
    }
    
    if (!percentage) {
        previewDiv.innerHTML = '<?php echo e(__('messages.Enter_percentage_to_see_preview')); ?>';
        return;
    }
    
    let preview = `<strong>${percentage}%</strong> <?php echo e(__('messages.discount_will_be_applied_to')); ?>: `;
    
    if (discountType === 'hourly') {
        preview += '<?php echo e(__('messages.Hourly_pricing_only')); ?>';
    } else if (discountType === 'service') {
        const allServices = document.getElementById('all_services') ? document.getElementById('all_services').checked : true;
        const selectedServices = document.querySelectorAll('.service-checkbox:checked').length;
        
        if (allServices || selectedServices === 0) {
            preview += '<?php echo e(__('messages.All_services')); ?>';
        } else {
            preview += `<?php echo e(__('messages.Selected_services')); ?> (${selectedServices})`;
        }
    } else {
        preview += '<?php echo e(__('messages.Both_hourly_and_all_services')); ?>';
    }
    
    previewDiv.innerHTML = preview;
}

// Event listeners
document.getElementById('percentage').addEventListener('input', updatePreview);

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePreview();
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/discounts/create.blade.php ENDPATH**/ ?>