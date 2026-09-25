<?php
// Central place for all revenue-related rates 
define('PLATFORM_COMMISSION_RATE', 0.03); // 3% platform cut on product subtotal
define('TAX_RATE', 0.16);                 // Kenyan VAT
define('DELIVERY_FEE', 150);              // fallback flat fee if coordinates are missing
define('DELIVERY_BASE_FEE', 100);         // KES, covers handling regardless of distance
define('DELIVERY_RATE_PER_KM', 15);       // KES per km beyond the base

/**
 * Straight-line distance in km between two lat/lng points (Haversine formula).
 */
function haversine_distance_km($lat1, $lng1, $lat2, $lng2) {
    $earth_radius = 6371; // km

    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLng / 2) * sin($dLng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earth_radius * $c;
}
//all above would need real routing a paid API like google maps distance matrix
/**
 * Delivery fee based on distance between pickup (artisan/warehouse) and
 * customer coordinates. Falls back to the flat DELIVERY_FEE if either
 * coordinate pair is missing.
 */
function calculate_delivery_fee($origin_lat, $origin_lng, $dest_lat, $dest_lng) {
    if (!$origin_lat || !$origin_lng || !$dest_lat || !$dest_lng) {
        return DELIVERY_FEE;
    }

    $distance_km = haversine_distance_km($origin_lat, $origin_lng, $dest_lat, $dest_lng);
    $fee = DELIVERY_BASE_FEE + ($distance_km * DELIVERY_RATE_PER_KM);

    return round($fee, 2);
}

/**
 * Given a product subtotal (sum of product_price * quantity, no delivery/tax),
 * returns every derived figure needed for the orders table and the M-Pesa charge.
 *
 * Pass origin/destination coordinates to price delivery by distance;
 * omit them (or pass nulls) to fall back to the flat DELIVERY_FEE.
 */
function calculate_order_breakdown($subtotal, $origin_lat = null, $origin_lng = null, $dest_lat = null, $dest_lng = null) {
    $commission = round($subtotal * PLATFORM_COMMISSION_RATE, 2);
    $tax        = round($subtotal * TAX_RATE, 2);
    $delivery   = calculate_delivery_fee($origin_lat, $origin_lng, $dest_lat, $dest_lng);

    $grand_total    = $subtotal + $tax + $delivery; // what the customer pays
    $artisan_payout = $subtotal - $commission;       // what the artisan is owed once delivered

    return [
        'subtotal'        => $subtotal,
        'commission'      => $commission,
        'tax'             => $tax,
        'delivery_fee'    => $delivery,
        'grand_total'     => $grand_total,
        'artisan_payout'  => $artisan_payout,
    ];
}