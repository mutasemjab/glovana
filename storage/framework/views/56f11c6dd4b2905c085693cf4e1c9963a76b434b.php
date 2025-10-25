<?php $__env->startSection('title'); ?>
    <?php echo e(__('messages.banners')); ?>

<?php $__env->stopSection(); ?>



<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title card_title_center"> <?php echo e(__('messages.banners')); ?> </h3>
        <a href="<?php echo e(route('banners.create')); ?>" class="btn btn-sm btn-success">
            <?php echo e(__('messages.New')); ?> <?php echo e(__('messages.banners')); ?>

        </a>
    </div>

    <div class="card-body">
        <div class="clearfix mb-3"></div>

        <div id="ajax_responce_serarchDiv" class="col-md-12">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('banner-table')): ?>
                <?php if(isset($data) && count($data) > 0): ?>
                    <table class="table table-bordered table-hover">
                        <thead class="custom_thead">
                            <tr>
                                <th><?php echo e(__('messages.Photo')); ?></th>
                                <th><?php echo e(__('messages.Type')); ?></th>
                                <th><?php echo e(__('messages.Product')); ?></th>
                                <th><?php echo e(__('messages.Provider Type')); ?></th>
                                <th><?php echo e(__('messages.Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <img class="custom_img"
                                             src="<?php echo e(asset('assets/admin/uploads/' . $info->photo)); ?>"
                                             alt="Banner Image" height="50">
                                    </td>
                                    <td>
                                        <?php echo e($info->type == 1 ? __('messages.Store') : __('messages.Provider Type')); ?>

                                    </td>
                                    <td>
                                        <?php echo e($info->product->name ?? '-'); ?>

                                    </td>
                                    <td>
                                        <?php echo e($info->providerType->name ?? '-'); ?>

                                    </td>
                                    <td>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('banner-delete')): ?>
                                            <a href="<?php echo e(route('banners.edit', $info->id)); ?>"
                                               class="btn btn-sm btn-primary"><?php echo e(__('messages.Edit')); ?></a>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('banner-delete')): ?>
                                            <form action="<?php echo e(route('banners.destroy', $info->id)); ?>"
                                                  method="POST"
                                                  onsubmit="return confirmDelete(event)"
                                                  style="display: inline-block;">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                        class="btn btn-sm btn-danger"><?php echo e(__('messages.Delete')); ?></button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                    <br>
                    <?php echo e($data->links()); ?>

                <?php else: ?>
                    <div class="alert alert-danger">
                        <?php echo e(__('messages.No_data')); ?>

                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    function confirmDelete(event) {
        event.preventDefault();
        if (confirm("Are you sure you want to delete this banner?")) {
            event.target.submit();
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\glovana\resources\views/admin/banners/index.blade.php ENDPATH**/ ?>