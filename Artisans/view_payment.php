<?php
include('../includes/pricing.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'artisan') {
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

// ---- Pull this artisan's product lines across all orders ----
// Payout is computed per line item here (not read from orders.artisan_payout),
// because one invoice can contain products from several artisans — the
// order-level stored payout isn't necessarily this artisan's share alone.
$payments_stmt = mysqli_prepare($conn, "
    SELECT
        o.invoice_number,
        o.order_date,
        o.order_status,
        o.delivery_confirmed_at,
        p.product_name,
        op.quantity,
        p.product_price
    FROM order_pending op
    JOIN products p ON op.product_id = p.product_id
    JOIN orders o    ON op.invoice_number = o.invoice_number AND op.customer_id = o.customer_id
    WHERE p.artisan_id = ?
    ORDER BY o.order_date DESC
");
mysqli_stmt_bind_param($payments_stmt, "i", $artisan_id);
mysqli_stmt_execute($payments_stmt);
$payments_result = mysqli_stmt_get_result($payments_stmt);

$rows = [];
$total_released  = 0; // paid AND delivery confirmed
$total_pending   = 0; // paid, awaiting delivery confirmation
$total_unpaid    = 0; // not paid yet — not owed until customer pays

while ($row = mysqli_fetch_assoc($payments_result)) {
    $line_subtotal  = $row['product_price'] * $row['quantity'];
    $line_commission = round($line_subtotal * PLATFORM_COMMISSION_RATE, 2);
    $line_payout     = $line_subtotal - $line_commission;

    $is_paid      = ($row['order_status'] === 'complete');
    $is_delivered = !empty($row['delivery_confirmed_at']);

    if ($is_paid && $is_delivered) {
        $release_status = 'Released';
        $release_class  = 'status-released';
        $total_released += $line_payout;
    } elseif ($is_paid) {
        $release_status = 'Pending delivery confirmation';
        $release_class  = 'status-pending';
        $total_pending += $line_payout;
    } else {
        $release_status = 'Awaiting customer payment';
        $release_class  = 'status-unpaid';
        $total_unpaid += $line_payout;
    }

    $row['line_subtotal']   = $line_subtotal;
    $row['line_commission'] = $line_commission;
    $row['line_payout']     = $line_payout;
    $row['release_status']  = $release_status;
    $row['release_class']   = $release_class;
    $rows[] = $row;
}
mysqli_stmt_close($payments_stmt);
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
    margin-top: 20px;
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
.summary-cards{
    display:flex;
    gap:16px;
    margin-top:8px;
    flex-wrap:wrap;
}
.summary-card{
    flex:1;
    min-width:200px;
    background:#f8f9fa;
    border:1px solid #eef1f4;
    border-radius:10px;
    padding:18px;
    text-align:center;
}
.summary-card span:first-child{
    font-size: 13px;
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.summary-card .amount{
    font-size:1.4rem;
    font-weight:700;
    display:block;
    margin-top:8px;
}
.status-badge{
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.status-released{
    background-color: #e6f7e8;
    color: #1a8a24;
}
.status-pending{
    background-color: #fdf1e0;
    color: #b3701a;
}
.status-unpaid{
    background-color: #eef1f4;
    color: #5a6b82;
}
</style>

<div class="products-card">
<h3>Your Payments</h3>

<div class="summary-cards">
    <div class="summary-card">
        <span>Released to you</span>
        <span class="amount" style="color:#1a7f37;">Ksh <?php echo number_format($total_released, 2); ?></span>
    </div>
    <div class="summary-card">
        <span>Paid — awaiting delivery confirmation</span>
        <span class="amount" style="color:#a15c00;">Ksh <?php echo number_format($total_pending, 2); ?></span>
    </div>
    <div class="summary-card">
        <span>Awaiting customer payment</span>
        <span class="amount" style="color:#5a6b82;">Ksh <?php echo number_format($total_unpaid, 2); ?></span>
    </div>
</div>

<table class="table1">
    <thead>
        <tr>
            <th>Invoice #</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th>Commission (3%)</th>
            <th>Your Payout</th>
            <th>Order Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($rows) === 0): ?>
            <tr><td colspan="8" style="text-align:center;">No payments yet.</td></tr>
        <?php else: foreach ($rows as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['invoice_number']); ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo (int) $row['quantity']; ?></td>
                <td>Ksh <?php echo number_format($row['line_subtotal'], 2); ?></td>
                <td>Ksh <?php echo number_format($row['line_commission'], 2); ?></td>
                <td>Ksh <?php echo number_format($row['line_payout'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                <td><span class="status-badge <?php echo $row['release_class']; ?>"><?php echo htmlspecialchars($row['release_status']); ?></span></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
</div>