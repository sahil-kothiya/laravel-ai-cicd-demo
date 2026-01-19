

<?php $__env->startSection('title', 'User Details'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
            <i class="fas fa-user text-primary"></i> User Details
        </h1>
        <div>
            <a href="<?php echo e(route('users.edit', $user->id)); ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="<?php echo e(route('users.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> User Information
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th width="200">ID</th>
                                <td>#<?php echo e($user->id); ?></td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td><?php echo e($user->name); ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?php echo e($user->email); ?></td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td><?php echo e($user->phone ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Age</th>
                                <td><?php echo e($user->age ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="<?php echo e(getStatusBadge($user->status)); ?>">
                                        <?php echo e(ucfirst($user->status)); ?>

                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Email Verified</th>
                                <td>
                                    <?php if($user->email_verified_at): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Verified
                                        </span>
                                        <small class="text-muted"><?php echo e(formatDateTime($user->email_verified_at)); ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-times"></i> Not Verified
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td><?php echo e(formatDateTime($user->created_at)); ?></td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td><?php echo e(formatDateTime($user->updated_at)); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-cog"></i> Actions
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('users.edit', $user->id)); ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit User
                        </a>
                        <form action="<?php echo e(route('users.toggleStatus', $user->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-toggle-on"></i> Toggle Status
                            </button>
                        </form>
                        <form action="<?php echo e(route('users.destroy', $user->id)); ?>" method="POST" id="delete-form">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="button" class="btn btn-danger w-100" onclick="confirmDelete('delete-form')">
                                <i class="fas fa-trash"></i> Delete User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\wamp64\www\SeesionDemo\resources\views/users/show.blade.php ENDPATH**/ ?>