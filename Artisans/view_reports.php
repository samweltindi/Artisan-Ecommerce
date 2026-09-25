<?php
if (!isset($_SESSION['username'])) {
    echo "<script>window.location.href='../customers/logout.php';</script>";
    exit;
}
// the username itself as "i" against p.artisan_id never matched any rows.
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

// ---- Filters ----
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
    SELECT op.invoice_number, p.product_name, op.quantity, p.product_price,
           (op.quantity * p.product_price) AS line_total, o.order_status,
           o.order_date, c.first_name
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
    // Only count paid orders toward revenue, items sold, and the order count -
    // an unpaid/pending order was never actually "sold".
    if ($row['order_status'] === 'complete') {
        $total_revenue += $row['line_total'];
        $total_items += $row['quantity'];
        $invoice_set[$row['invoice_number']] = true;
    }
}
$total_orders = count($invoice_set);
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
.report-filters{
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:16px;
    margin-bottom: 20px;
}
.report-filters label{
    font-size: 14px;
    color: #4a5568;
    font-weight: 600;
}
.report-filters input, .report-filters select{
    margin-left: 6px;
    padding: 8px 10px;
    border: 1px solid #dfe3e8;
    border-radius: 6px;
    font-size: 14px;
}
.report-filters button{
    background-color: #0cc012;
    color: white;
    border: none;
    padding: 9px 18px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
}
.report-links{
    margin-bottom: 24px;
    font-size: 14px;
}
.report-links a{
    color: #0cc012;
    font-weight: 600;
    text-decoration: none;
}
.report-links a:hover{
    text-decoration: underline;
}
.summary-cards{
    display:flex;
    gap:16px;
    margin-bottom: 24px;
    flex-wrap:wrap;
}
.summary-card{
    flex:1;
    min-width:180px;
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
    color: #1c2b3a;
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
.status-complete{
    background-color: #e6f7e8;
    color: #1a8a24;
}
.status-pending{
    background-color: #fdf1e0;
    color: #b3701a;
}
</style>

<div class="products-card">
<h3>Sales Report</h3>

<form method="GET" class="report-filters">
    <input type="hidden" name="view_reports" value="1">
    <label>From: <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>"></label>
    <label>To: <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>"></label>
    <label>Status:
        <select name="status">
            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All</option>
            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="complete" <?= $status_filter === 'complete' ? 'selected' : '' ?>>Complete</option>
        </select>
    </label>
    <button type="submit">Apply</button>
</form>

<p class="report-links">
    <a href="export_reports_csv.php?date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&status=<?= urlencode($status_filter) ?>" target="_blank">Export CSV</a>
    &nbsp;|&nbsp;
    <a href="print_reports.php?date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&status=<?= urlencode($status_filter) ?>" target="_blank">Printable version</a>
</p>

<div class="summary-cards">
    <div class="summary-card">
        <span>Total Revenue</span>
        <span class="amount">KSh <?= number_format($total_revenue, 2) ?></span>
    </div>
    <div class="summary-card">
        <span>Orders</span>
        <span class="amount"><?= $total_orders ?></span>
    </div>
    <div class="summary-card">
        <span>Items Sold</span>
        <span class="amount"><?= $total_items ?></span>
    </div>
</div>

<table class="table1">
    <thead>
        <tr>
            <th>Date</th><th>Invoice #</th><th>Customer</th><th>Product</th>
            <th>Qty</th><th>Unit Price</th><th>Line Total</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="8" style="text-align:center;">No orders found for this range.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['order_date']) ?></td>
                <td><?= htmlspecialchars($r['invoice_number']) ?></td>
                <td><?= htmlspecialchars($r['first_name']) ?></td>
                <td><?= htmlspecialchars($r['product_name']) ?></td>
                <td><?= (int)$r['quantity'] ?></td>
                <td>KSh <?= number_format($r['product_price'], 2) ?></td>
                <td>KSh <?= number_format($r['line_total'], 2) ?></td>
                <td><span class="status-badge status-<?= htmlspecialchars($r['order_status']) ?>"><?= htmlspecialchars($r['order_status']) ?></span></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
</div>