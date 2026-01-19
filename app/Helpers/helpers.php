<?php

if (!function_exists('formatPrice')) {
    /**
     * Format price with currency symbol
     *
     * @param float $price
     * @param string $currency
     * @return string
     */
    function formatPrice(float $price, string $currency = '$'): string
    {
        return $currency . number_format($price, 2);
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format date for display
     *
     * @param string|\Carbon\Carbon $date
     * @param string $format
     * @return string
     */
    function formatDate($date, string $format = 'M d, Y'): string
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->format($format);
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Format datetime for display
     *
     * @param string|\Carbon\Carbon $datetime
     * @param string $format
     * @return string
     */
    function formatDateTime($datetime, string $format = 'M d, Y H:i A'): string
    {
        if (is_string($datetime)) {
            $datetime = \Carbon\Carbon::parse($datetime);
        }
        
        return $datetime->format($format);
    }
}

if (!function_exists('getStatusBadge')) {
    /**
     * Get Bootstrap badge class for status
     *
     * @param string $status
     * @param string $type
     * @return string
     */
    function getStatusBadge(string $status, string $type = 'general'): string
    {
        $badges = [
            'general' => [
                'active' => 'success',
                'inactive' => 'secondary',
                'suspended' => 'danger',
            ],
            'order' => [
                'pending' => 'warning',
                'processing' => 'info',
                'completed' => 'success',
                'cancelled' => 'danger',
            ],
        ];

        $badgeType = $badges[$type][$status] ?? 'secondary';
        
        return "badge bg-{$badgeType}";
    }
}

if (!function_exists('getOrderStatusBadge')) {
    /**
     * Get Bootstrap badge class for order status
     *
     * @param string $status
     * @return string
     */
    function getOrderStatusBadge(string $status): string
    {
        $badges = [
            'pending' => 'badge bg-warning text-dark',
            'processing' => 'badge bg-info',
            'completed' => 'badge bg-success',
            'cancelled' => 'badge bg-danger',
        ];

        return $badges[$status] ?? 'badge bg-secondary';
    }
}

if (!function_exists('truncateText')) {
    /**
     * Truncate text to specified length
     *
     * @param string $text
     * @param int $length
     * @param string $suffix
     * @return string
     */
    function truncateText(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
}

if (!function_exists('successResponse')) {
    /**
     * Return standardized success JSON response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    function successResponse($data = null, string $message = 'Success', int $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}

if (!function_exists('errorResponse')) {
    /**
     * Return standardized error JSON response
     *
     * @param string $message
     * @param int $statusCode
     * @param array|null $errors
     * @return \Illuminate\Http\JsonResponse
     */
    function errorResponse(string $message = 'Error', int $statusCode = 400, ?array $errors = null): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}

if (!function_exists('generateSKU')) {
    /**
     * Generate unique SKU
     *
     * @param string $prefix
     * @return string
     */
    function generateSKU(string $prefix = 'PRD'): string
    {
        return strtoupper($prefix) . '-' . strtoupper(\Illuminate\Support\Str::random(8));
    }
}

if (!function_exists('calculatePercentage')) {
    /**
     * Calculate percentage
     *
     * @param float $value
     * @param float $total
     * @param int $decimals
     * @return float
     */
    function calculatePercentage(float $value, float $total, int $decimals = 2): float
    {
        if ($total == 0) {
            return 0;
        }
        
        return round(($value / $total) * 100, $decimals);
    }
}
