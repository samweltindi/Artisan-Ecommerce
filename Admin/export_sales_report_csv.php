<?php
// export_sales_report_csv.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include('../database_connection.php');
//guard rails the file from unauthorize user
if (empty($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Not authorized');
}

$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to   = $_GET['date_to'] ?? date('Y-m-d');
$status_filter = $_GET['status'] ?? 'all';

$statusClause = "";
$params = [$date_from, $date_to];
$types = "ss";
if ($status_filter !== 'all') {
    // o.order_status (orders table) is the authoritative payment status -
    // op.order_status (order_pending) is a snapshot from order creation and
    // is never updated once payment completes.
    $statusClause = " AND o.order_status = ? ";
    $params[] = $status_filter;
    $types .= "s";
}

$sql = "
    SELECT o.order_date, op.invoice_number, c.first_name, a.username AS artisan_name,
           p.product_name, op.quantity, p.product_price, (op.quantity * p.product_price) AS line_total, o.order_status
    FROM order_pending op
    JOIN products p ON op.product_id = p.product_id
    JOIN orders o ON op.invoice_number = o.invoice_number
    JOIN customers c ON o.customer_id = c.customer_id
    JOIN artisans a ON p.artisan_id = a.artisan_id
    WHERE DATE(o.order_date) BETWEEN ? AND ?
      $statusClause
    ORDER BY o.order_date DESC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="system_sales_report_' . $date_from . '_to_' . $date_to . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Date', 'Invoice #', 'Customer', 'Artisan', 'Product', 'Qty', 'Unit Price', 'Line Total', 'Status']);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($out, [
        $row['order_date'], $row['invoice_number'], $row['first_name'], $row['artisan_name'],
        $row['product_name'], $row['quantity'], $row['product_price'],
        $row['line_total'], $row['order_status']
    ]);
}
fclose($out);