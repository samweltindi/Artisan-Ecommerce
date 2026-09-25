<?php
// export_reports_csv.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include('../database_connection.php');

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    exit('Not authorized');
}


// Resolve the real integer artisan_id from the session username -
// binding the username itself as "i" against p.artisan_id never matched
// any rows correctly.
$artisan_username = $_SESSION['username'];
$artisan_stmt = mysqli_prepare($conn, "SELECT artisan_id FROM artisans WHERE username = ?");
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

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sales_report_' . $date_from . '_to_' . $date_to . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Date', 'Invoice #', 'Customer', 'Product', 'Qty', 'Unit Price', 'Line Total', 'Status']);

while ($row = mysqli_fetch_assoc($result)) {
    // Column names must match the SELECT above: first_name / product_price,
    // not customer_name / price (those keys never existed in the result set).
    fputcsv($out, [
        $row['order_date'], $row['invoice_number'], $row['first_name'],
        $row['product_name'], $row['quantity'], $row['product_price'],
        $row['line_total'], $row['order_status']
    ]);
}
fclose($out);