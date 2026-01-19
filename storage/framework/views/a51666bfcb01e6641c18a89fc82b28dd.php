

<?php $__env->startSection('title', 'Dashboard - Laravel AI CI/CD Demo'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
            <i class="fas fa-tachometer-alt text-primary"></i> Dashboard
        </h1>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Users</h6>
                        <h3 class="mb-0"><?php echo e($statistics['users']['total']); ?></h3>
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i> <?php echo e($statistics['users']['active']); ?> Active
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Products</h6>
                        <h3 class="mb-0"><?php echo e($statistics['products']['total']); ?></h3>
                        <small class="text-warning">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo e($statistics['products']['low_stock']); ?> Low Stock
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="icon bg-info bg-opacity-10 text-info me-3">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Orders</h6>
                        <h3 class="mb-0"><?php echo e($statistics['orders']['total']); ?></h3>
                        <small class="text-info">
                            <i class="fas fa-clock"></i> <?php echo e($statistics['orders']['pending']); ?> Pending
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Revenue</h6>
                        <h3 class="mb-0"><?php echo e(formatPrice($statistics['orders']['total_revenue'])); ?></h3>
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> From Completed Orders
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Status Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar"></i> Order Status Summary
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h5 class="text-warning"><?php echo e($statistics['orders']['pending']); ?></h5>
                            <p class="mb-0">Pending</p>
                        </div>
                        <div class="col-md-3">
                            <h5 class="text-info"><?php echo e($statistics['orders']['processing']); ?></h5>
                            <p class="mb-0">Processing</p>
                        </div>
                        <div class="col-md-3">
                            <h5 class="text-success"><?php echo e($statistics['orders']['completed']); ?></h5>
                            <p class="mb-0">Completed</p>
                        </div>
                        <div class="col-md-3">
                            <h5 class="text-danger"><?php echo e($statistics['orders']['cancelled']); ?></h5>
                            <p class="mb-0">Cancelled</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Orders -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list"></i> Recent Orders</span>
                    <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if(count($recentOrders) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Product</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><strong><?php echo e($order->order_number); ?></strong></td>
                                            <td><?php echo e($order->user->name); ?></td>
                                            <td><?php echo e($order->product->name); ?></td>
                                            <td><?php echo e(formatPrice($order->total_price)); ?></td>
                                            <td>
                                                <span class="<?php echo e(getStatusBadge($order->status, 'order')); ?>">
                                                    <?php echo e(ucfirst($order->status)); ?>

                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No recent orders</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</span>
                    <a href="<?php echo e(route('products.index')); ?>" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if(count($lowStockProducts) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php $__currentLoopData = $lowStockProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo e($product->name); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo e($product->sku); ?></small>
                                    </div>
                                    <span class="badge bg-danger"><?php echo e($product->stock); ?> left</span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                            <p>All products well stocked!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bolt"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus"></i><br>Add User
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo e(route('products.create')); ?>" class="btn btn-success w-100">
                                <i class="fas fa-box"></i><br>Add Product
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo e(route('orders.create')); ?>" class="btn btn-info w-100">
                                <i class="fas fa-shopping-cart"></i><br>Create Order
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-warning w-100">
                                <i class="fas fa-list"></i><br>View Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\wamp64\www\SeesionDemo\resources\views/dashboard.blade.php ENDPATH**/ ?>