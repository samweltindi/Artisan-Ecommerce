<?php
// This file is meant to be include()'d mid-page from Artisans/index.php,
// same pattern as your other tabs (Insert Products, View products, etc.)
// — so no <html>/<head> here, and redirects use the echo-script workaround
// since header() won't work after HTML output has already started.

if (!isset($_SESSION['username'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

// ---- Resolve the logged-in artisan's ID ----
$artisan_username = $_SESSION['username'];
$artisan_stmt = mysqli_prepare($conn, "SELECT artisan_id FROM artisans WHERE username = ?");
mysqli_stmt_bind_param($artisan_stmt, "s", $artisan_username);
mysqli_stmt_execute($artisan_stmt);
$artisan_row = mysqli_fetch_assoc(mysqli_stmt_get_result($artisan_stmt));
mysqli_stmt_close($artisan_stmt);

if (!$artisan_row) {
    echo "<p>Artisan account not found.</p>";
    exit;
}
$artisan_id = $artisan_row['artisan_id'];

// ---- Pull only order_pending rows for products belonging to THIS artisan ----
$orders_stmt = mysqli_prepare($conn, "
    SELECT
        o.invoice_number,
        o.order_date,
        o.order_status,
        o.delivery_confirmed_at,
        c.username AS customer_username,
        p.product_name,
        op.quantity,
        p.product_price
    FROM order_pending op
    JOIN products p  ON op.product_id = p.product_id
    JOIN customers c ON op.customer_id = c.customer_id
    JOIN orders o     ON op.invoice_number = o.invoice_number AND op.customer_id = o.customer_id
    WHERE p.artisan_id = ?
    ORDER BY o.order_date DESC
");
mysqli_stmt_bind_param($orders_stmt, "i", $artisan_id);
mysqli_stmt_execute($orders_stmt);
$orders_result = mysqli_stmt_get_result($orders_stmt);
?>
<style>
.products-card{
    max-width: 1100px;
    margin: 20px auto;
    padding: 40px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #eef1f4;
}
.products-card h3{
    text-align: left;
    color: #1c2b3a;
    font-size: 1.5rem;
    font-weight: 700;
    border-bottom: 1px solid #eef1f4;
    padding-bottom: 16px;
    margin: 0 0 24px 0;
}
.table1{
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
.table1 th, .table1 td{
    border: none;
    border-bottom: 1px solid #eef1f4;
    padding: 14px 12px;
    text-align: left;
}
.table1 thead th{
    background-color: #f8f9fa;
    color: #4a5568;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.table1 tbody tr:hover{
    background-color: #fafbfc;
}
.table1 tbody tr:last-child td{
    border-bottom: none;
}
.status-badge{
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.status-paid{
    background-color: #e6f7e8;
    color: #1a8a24;
}
.status-awaiting{
    background-color: #fdf1e0;
    color: #b3701a;
}
.status-delivered{
    color: #1a8a24;
    font-weight: 600;
}
.status-notconfirmed{
    color: #6b7280;
}
</style>

<div class="products-card">
<h3>Your Orders</h3>
<table class="table1">
    <thead>
        <tr>
            <th>Invoice #</th>
            <th>Customer</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Line Total</th>
            <th>Order Date</th>
            <th>Payment Status</th>
            <th>Delivery</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $found_any = false;
        while ($row = mysqli_fetch_assoc($orders_result)) {
            $found_any = true;
            $line_total = $row['product_price'] * $row['quantity'];
            $is_paid = ($row['order_status'] === 'complete');
            $payment_label = $is_paid ? 'Paid' : 'Awaiting payment';
            $payment_class = $is_paid ? 'status-paid' : 'status-awaiting';
            $is_delivered = !empty($row['delivery_confirmed_at']);
            $delivery_label = $is_delivered ? 'Delivered' : 'Not yet confirmed';
            $delivery_class = $is_delivered ? 'status-delivered' : 'status-notconfirmed';

            echo "<tr>
                    <td>" . htmlspecialchars($row['invoice_number']) . "</td>
                    <td>" . htmlspecialchars($row['customer_username']) . "</td>
                    <td>" . htmlspecialchars($row['product_name']) . "</td>
                    <td>" . (int) $row['quantity'] . "</td>
                    <td>Ksh " . number_format($line_total, 2) . "</td>
                    <td>" . htmlspecialchars($row['order_date']) . "</td>
                    <td><span class='status-badge $payment_class'>$payment_label</span></td>
                    <td><span class='$delivery_class'>$delivery_label</span></td>
                  </tr>";
        }
        if (!$found_any) {
            echo "<tr><td colspan='8' style='text-align:center;'>No orders yet.</td></tr>";
        }
        mysqli_stmt_close($orders_stmt);
        ?>
    </tbody>
</table>
</div>