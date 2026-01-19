@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
            <i class="fas fa-shopping-cart text-primary"></i> Order Details
        </h1>
        <div>
            <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Order Information
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th width="200">Order ID</th>
                                <td><strong>#{{ $order->id }}</strong></td>
                            </tr>
                            <tr>
                                <th>Order Number</th>
                                <td><code>{{ $order->order_number }}</code></td>
                            </tr>
                            <tr>
                                <th>Customer</th>
                                <td>
                                    <a href="{{ route('users.show', $order->user_id) }}">
                                        {{ $order->user->name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $order->user->email }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Product</th>
                                <td>
                                    <a href="{{ route('products.show', $order->product_id) }}">
                                        {{ $order->product->name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">SKU: {{ $order->product->sku }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Quantity</th>
                                <td><strong>{{ $order->quantity }}</strong> units</td>
                            </tr>
                            <tr>
                                <th>Unit Price</th>
                                <td>${{ number_format($order->unit_price, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Total Price</th>
                                <td><strong class="text-success">${{ number_format($order->total_price, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="{{ getOrderStatusBadge($order->status) }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Notes</th>
                                <td>{{ $order->notes ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Ordered At</th>
                                <td>{{ formatDateTime($order->ordered_at) }}</td>
                            </tr>
                            <tr>
                                <th>Processed At</th>
                                <td>{{ $order->processed_at ? formatDateTime($order->processed_at) : 'Not yet processed' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ formatDateTime($order->created_at) }}</td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td>{{ formatDateTime($order->updated_at) }}</td>
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
                        <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Order
                        </a>
                        <form action="{{ route('orders.destroy', $order->id) }}" method="POST" id="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger w-100" onclick="confirmDelete('delete-form')">
                                <i class="fas fa-trash"></i> Delete Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <i class="fas fa-calculator"></i> Order Summary
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-end">${{ number_format($order->unit_price * $order->quantity, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Tax (0%):</td>
                            <td class="text-end">$0.00</td>
                        </tr>
                        <tr class="fw-bold">
                            <td>Total:</td>
                            <td class="text-end text-success">${{ number_format($order->total_price, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
