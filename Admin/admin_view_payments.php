<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
include('../includes/pricing.php');

if (!isset($_SESSION['username'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
$admin_username = $_SESSION['username'];
$admin_stmt = mysqli_prepare($conn, "SELECT admin_id FROM admin WHERE username = ?");
mysqli_stmt_bind_param($admin_stmt, "s", $admin_username);
mysqli_stmt_execute($admin_stmt);
$admin_row = mysqli_fetch_assoc(mysqli_stmt_get_result($admin_stmt));
mysqli_stmt_close($admin_stmt);

if (!$admin_row) {
    echo "<p>Access denied.</p>";
    exit;
}

// =====================================================================
// SECTION 1: Payments received FROM customers (M-Pesa transactions)
// =====================================================================
$customer_payments_stmt = mysqli_prepare($conn, "
    SELECT
        mt.invoice_number,
        mt.phone,
        mt.amount,
        mt.status,
        mt.result_description,
        mt.created_at,
        c.username AS customer_username
    FROM mpesa_transactions mt
    LEFT JOIN orders o     ON mt.invoice_number = o.invoice_number
    LEFT JOIN customers c  ON o.customer_id = c.customer_id
    ORDER BY mt.created_at DESC
");
mysqli_stmt_execute($customer_payments_stmt);
$customer_payments_result = mysqli_stmt_get_result($customer_payments_stmt);

$customer_rows = [];
$total_collected = 0;
while ($row = mysqli_fetch_assoc($customer_payments_result)) {
    if ($row['status'] === 'completed') {
        $total_collected += $row['amount'];
    }
    $customer_rows[] = $row;
}
mysqli_stmt_close($customer_payments_stmt);

// =====================================================================
// SECTION 2: Payouts OWED TO artisans (per product line, across all orders)
// =====================================================================
$artisan_payouts_stmt = mysqli_prepare($conn, "
    SELECT
        a.artisan_id,
        a.username AS artisan_username,
        o.invoice_number,
        o.order_date,
        o.order_status,
        o.delivery_confirmed_at,
        p.product_name,
        op.quantity,
        p.product_price
    FROM order_pending op
    JOIN products p  ON op.product_id = p.product_id
    JOIN artisans a  ON p.artisan_id = a.artisan_id
    JOIN orders o     ON op.invoice_number = o.invoice_number AND op.customer_id = o.customer_id
    ORDER BY a.username, o.order_date DESC
");
mysqli_stmt_execute($artisan_payouts_stmt);
$artisan_payouts_result = mysqli_stmt_get_result($artisan_payouts_stmt);

$payout_lines = [];
$platform_commission_total = 0;
$total_released = 0;
$total_pending  = 0;
$total_unpaid   = 0;

// Also build a per-artisan summary as we go
$artisan_summary = []; // artisan_id => ['username'=>, 'released'=>, 'pending'=>, 'unpaid'=>]

while ($row = mysqli_fetch_assoc($artisan_payouts_result)) {
    $line_subtotal   = $row['product_price'] * $row['quantity'];
    $line_commission = round($line_subtotal * PLATFORM_COMMISSION_RATE, 2);
    $line_payout     = $line_subtotal - $line_commission;

    $is_paid      = ($row['order_status'] === 'complete');
    $is_delivered = !empty($row['delivery_confirmed_at']);

    if ($is_paid && $is_delivered) {
        $release_status = 'Released';
        $total_released += $line_payout;
        $bucket = 'released';
    } elseif ($is_paid) {
        $release_status = 'Pending delivery confirmation';
        $total_pending += $line_payout;
        $bucket = 'pending';
    } else {
        $release_status = 'Awaiting customer payment';
        $total_unpaid += $line_payout;
        $bucket = 'unpaid';
    }

    if ($is_paid) {
        $platform_commission_total += $line_commission;
    }

    $aid = $row['artisan_id'];
    if (!isset($artisan_summary[$aid])) {
        $artisan_summary[$aid] = ['username' => $row['artisan_username'], 'released' => 0, 'pending' => 0, 'unpaid' => 0];
    }
    $artisan_summary[$aid][$bucket] += $line_payout;

    $row['line_subtotal']   = $line_subtotal;
    $row['line_commission'] = $line_commission;
    $row['line_payout']     = $line_payout;
    $row['release_status']  = $release_status;
    $payout_lines[] = $row;
}
mysqli_stmt_close($artisan_payouts_stmt);
?>

<style>
.products-card{
    max-width: 1200px;
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
    margin-top: 16px;
    margin-bottom: 32px;
    font-size: 14px;
}
.table1 th, .table1 td{
    border: none;
    border-bottom: 1px solid #eef1f4;
    padding: 12px;
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
.summary-cards{
    display:flex;
    gap:16px;
    margin-top:8px;
    margin-bottom: 8px;
    flex-wrap:wrap;
}
.summary-card{
    flex:1;
    min-width:170px;
    background:#f8f9fa;
    border:1px solid #eef1f4;
    border-radius:10px;
    padding:16px;
    text-align:center;
}
.summary-card span:first-child{
    font-size: 12px;
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.summary-card .amount{
    font-size:1.2rem;
    font-weight:700;
    display:block;
    margin-top:6px;
}
.section-title{
    color:#1c2b3a;
    font-size: 1.1rem;
    margin-top:30px;
    margin-bottom: 4px;
}
</style>

<div class="products-card">
<h3>Payments Overview</h3>

<div class="summary-cards">
    <div class="summary-card">
        <span>Total collected from customers</span>
        <span class="amount" style="color:#1a7f37;">Ksh <?php echo number_format($total_collected, 2); ?></span>
    </div>
    <div class="summary-card">
        <span>Platform commission earned</span>
        <span class="amount" style="color:#35c7c9;">Ksh <?php echo number_format($platform_commission_total, 2); ?></span>
    </div>
    <div class="summary-card">
        <span>Released to artisans</span>
        <span class="amount" style="color:#1a7f37;">Ksh <?php echo number_format($total_released, 2); ?></span>
    </div>
    <div class="summary-card">
        <span>Pending delivery confirmation</span>
        <span class="amount" style="color:#a15c00;">Ksh <?php echo number_format($total_pending, 2); ?></span>
    </div>
    <div class="summary-card">
        <span>Awaiting customer payment</span>
        <span class="amount" style="color:#5a6b82;">Ksh <?php echo number_format($total_unpaid, 2); ?></span>
    </div>
</div>

<!-- ===================== Customer Payments ===================== -->
<h3 class="section-title">Payments by Customers</h3>
<table class="table1">
    <thead>
        <tr>
            <th>Invoice #</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Result</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($customer_rows) === 0): ?>
            <tr><td colspan="7" style="text-align:center;">No payments yet.</td></tr>
        <?php else: foreach ($customer_rows as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['invoice_number']); ?></td>
                <td><?php echo htmlspecialchars($row['customer_username'] ?? 'Unknown'); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td>Ksh <?php echo number_format($row['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                <td><?php echo htmlspecialchars($row['result_description'] ?? '—'); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>

<!-- ===================== Per-Artisan Payout Summary ===================== -->
<h3 class="section-title">Payouts by Artisan (Summary)</h3>
<table class="table1">
    <thead>
        <tr>
            <th>Artisan</th>
            <th>Released</th>
            <th>Pending delivery confirmation</th>
            <th>Awaiting customer payment</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($artisan_summary) === 0): ?>
            <tr><td colspan="4" style="text-align:center;">No artisan payouts yet.</td></tr>
        <?php else: foreach ($artisan_summary as $summary): ?>
            <tr>
                <td><?php echo htmlspecialchars($summary['username']); ?></td>
                <td>Ksh <?php echo number_format($summary['released'], 2); ?></td>
                <td>Ksh <?php echo number_format($summary['pending'], 2); ?></td>
                <td>Ksh <?php echo number_format($summary['unpaid'], 2); ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>

<!-- ===================== Full Line-Item Payout Detail ===================== -->
<h3 class="section-title">Payouts by Artisan (Detail)</h3>
<table class="table1">
    <thead>
        <tr>
            <th>Artisan</th>
            <th>Invoice #</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th>Commission</th>
            <th>Payout</th>
            <th>Order Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($payout_lines) === 0): ?>
            <tr><td colspan="9" style="text-align:center;">No payout lines yet.</td></tr>
        <?php else: foreach ($payout_lines as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['artisan_username']); ?></td>
                <td><?php echo htmlspecialchars($row['invoice_number']); ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo (int) $row['quantity']; ?></td>
                <td>Ksh <?php echo number_format($row['line_subtotal'], 2); ?></td>
                <td>Ksh <?php echo number_format($row['line_commission'], 2); ?></td>
                <td>Ksh <?php echo number_format($row['line_payout'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                <td><?php echo htmlspecialchars($row['release_status']); ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
</div>