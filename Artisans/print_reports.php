<?php
// print_reports.php
// Renders an actual printable HTML report - this file used to be a byte-for-byte
// copy of export_reports_csv.php, so "Printable version" silently downloaded a
// second CSV instead of showing anything printable.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include('../database_connection.php');

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    exit('Not authorized');
}

$artisan_username = $_SESSION['username'];
$artisan_stmt = mysqli_prepare($conn, "SELECT artisan_id, full_name, business_name FROM artisans WHERE username = ?");
mysqli_stmt_bind_param($artisan_stmt, "s", $artisan_username);
mysqli_stmt_execute($artisan_stmt);
$artisan_row = mysqli_fetch_assoc(mysqli_stmt_get_result($artisan_stmt));
mysqli_stmt_close($artisan_stmt);

if (!$artisan_row) {
    http_response_code(403);
    exit('Artisan account not found.');
}
$artisan_id = $artisan_row['artisan_id'];

$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to   = $_GET['date_to'] ?? date('Y-m-d');
$status_filter = $_GET['status'] ?? 'all';

$statusClause = "";
$params = [$artisan_id, $date_from, $date_to];
$types = "iss";
if ($status_filter !== 'all') {
    // o.order_status (orders table) is the authoritative payment status -
    // op.order_status (order_pending) is a snapshot from order creation and
    // is never updated once payment completes.
    $statusClause = " AND o.order_status = ? ";
    $params[] = $status_filter;
    $types .= "s";
}

$sql = "
    SELECT o.order_date, op.invoice_number, c.first_name, p.product_name,
           op.quantity, p.product_price, (op.quantity * p.product_price) AS line_total, o.order_status
    FROM order_pending op
    JOIN products p ON op.product_id = p.product_id
    JOIN orders o ON op.invoice_number = o.invoice_number
    JOIN customers c ON o.customer_id = c.customer_id
    WHERE p.artisan_id = ?
      AND DATE(o.order_date) BETWEEN ? AND ?
      $statusClause
    ORDER BY o.order_date DESC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$rows = [];
$total_revenue = 0;
$total_items = 0;
$invoice_set = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
    if ($row['order_status'] === 'complete') {
        $total_revenue += $row['line_total'];
    }
    $total_items += $row['quantity'];
    $invoice_set[$row['invoice_number']] = true;
}
$total_orders = count($invoice_set);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Report - <?= htmlspecialchars($date_from) ?> to <?= htmlspecialchars($date_to) ?></title>
<style>
    body{ font-family: Arial, Helvetica, sans-serif; color:#222; margin: 30px; }
    h1{ color:green; margin-bottom: 0; }
    .subtitle{ color:#555; margin-top:4px; margin-bottom: 20px; }
    table{ width:100%; border-collapse: collapse; margin-bottom: 24px; }
    th, td{ border:1px solid #999; padding: 6px 10px; text-align:left; }
    th{ background:#f2f2f2; }
    .summary td{ font-weight:bold; }
    .print-btn{ margin-bottom: 20px; padding: 8px 16px; background:#0E4D3C; color:white; border:none; cursor:pointer; }
    .back-link{ margin-bottom: 20px; margin-left: 10px; padding: 8px 16px; background:#eef1f4; color:#1c2b3a; border:none; border-radius:4px; text-decoration:none; display:inline-block; font-family: inherit; font-size: 14px; }
    @media print {
        .print-btn{ display:none; }
        .back-link{ display:none; }
    }
</style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print this report</button>
    <a href="index.php" class="back-link">&larr; Back to account</a>
    <h1>Sales Report</h1>
    <p class="subtitle">
        <?= htmlspecialchars($artisan_row['business_name'] ?: $artisan_row['full_name']) ?>
        &mdash; <?= htmlspecialchars($date_from) ?> to <?= htmlspecialchars($date_to) ?>
        <?= $status_filter !== 'all' ? ' &mdash; Status: ' . htmlspecialchars($status_filter) : '' ?>
    </p>

    <table class="summary">
        <tr><th>Total Revenue</th><th>Orders</th><th>Items Sold</th></tr>
        <tr>
            <td>KSh <?= number_format($total_revenue, 2) ?></td>
            <td><?= $total_orders ?></td>
            <td><?= $total_items ?></td>
        </tr>
    </table>

    <table>
        <tr>
            <th>Date</th><th>Invoice #</th><th>Customer</th><th>Product</th>
            <th>Qty</th><th>Unit Price</th><th>Line Total</th><th>Status</th>
        </tr>
        <?php if (empty($rows)): ?>
            <tr><td colspan="8">No orders found for this range.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['order_date']) ?></td>
                <td><?= htmlspecialchars($r['invoice_number']) ?></td>
                <td><?= htmlspecialchars($r['first_name']) ?></td>
                <td><?= htmlspecialchars($r['product_name']) ?></td>
                <td><?= (int) $r['quantity'] ?></td>
                <td>KSh <?= number_format($r['product_price'], 2) ?></td>
                <td>KSh <?= number_format($r['line_total'], 2) ?></td>
                <td><?= htmlspecialchars($r['order_status']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </table>
</body>
</html>