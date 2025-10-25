<?php $__env->startSection('title', __('messages.Edit_provider')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(__('messages.Edit_provider')); ?></h1>
        <a href="<?php echo e(route('providers.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?php echo e(__('messages.Back_to_List')); ?>

        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.provider_Details')); ?></h6>
        </div>
        <div class="card-body">
            <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <?php endif; ?>

            <form action="<?php echo e(route('providers.update', $provider->id)); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="name"><?php echo e(__('messages.Name of manager')); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name_of_manager" name="name_of_manager" value="<?php echo e(old('name_of_manager', $provider->name_of_manager)); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone"><?php echo e(__('messages.Phone')); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo e(old('phone', $provider->phone)); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email"><?php echo e(__('messages.Email')); ?></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo e(old('email', $provider->email)); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="password"><?php echo e(__('messages.Password')); ?></label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="form-text text-muted"><?php echo e(__('messages.Leave_blank_to_keep_current_password')); ?></small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Additional Information -->
                        <div class="form-group">
                            <label for="balance"><?php echo e(__('messages.Balance')); ?></label>
                            <input type="number" step="0.01" class="form-control" id="balance" name="balance" value="<?php echo e(old('balance', $provider->balance)); ?>">
                        </div>
                        
                        
                        <div class="form-group">
                            <label for="activate"><?php echo e(__('messages.Status')); ?></label>
                            <select class="form-control" id="activate" name="activate">
                                <option value="1" <?php echo e(old('activate', $provider->activate) == 1 ? 'selected' : ''); ?>><?php echo e(__('messages.Active')); ?></option>
                                <option value="2" <?php echo e(old('activate', $provider->activate) == 2 ? 'selected' : ''); ?>><?php echo e(__('messages.Inactive')); ?></option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="photo"><?php echo e(__('messages.Photo')); ?></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="photo" name="photo_of_manager">
                                <label class="custom-file-label" for="photo_of_manager"><?php echo e(__('messages.Choose_file')); ?></label>
                            </div>
                            <div class="mt-3" id="image-preview">
                                <?php if($provider->photo_of_manager): ?>
                                <img src="<?php echo e(asset('assets/admin/uploads/' . $provider->photo_of_manager)); ?>" class="img-fluid img-thumbnail" style="max-height: 200px;">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo e(__('messages.Update')); ?>

                    </button>
                    <a href="<?php echo e(route('providers.index')); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> <?php echo e(__('messages.Cancel')); ?>

                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    // Show image preview
    $(document).ready(function() {
        // Show filename on file select
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
            
            // Image preview
            if (this.files && this.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').html('<img src="' + e.target.result + '" class="img-fluid img-thumbnail" style="max-height: 200px;">');
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/providers/edit.blade.php ENDPATH**/ ?>