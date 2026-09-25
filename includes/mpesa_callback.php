<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../database_connection.php');

$callback_raw = file_get_contents('php://input');
$callback_data = json_decode($callback_raw, true);
file_put_contents(__DIR__ . '/mpesa_callback_log.txt', date('c') . " " . $callback_raw . PHP_EOL, FILE_APPEND);

$stk_callback = $callback_data['Body']['stkCallback'] ?? null;

if ($stk_callback) {
    $checkout_request_id = $stk_callback['CheckoutRequestID'];
    $result_code          = $stk_callback['ResultCode'];
    $result_desc           = $stk_callback['ResultDesc'];

    $status = ($result_code === 0) ? 'completed' : 'failed';

    // Find the invoice this transaction belongs to.
    $find_stmt = mysqli_prepare($conn, "SELECT invoice_number FROM mpesa_transactions WHERE checkout_request_id = ?");
    mysqli_stmt_bind_param($find_stmt, "s", $checkout_request_id);
    mysqli_stmt_execute($find_stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($find_stmt));
    mysqli_stmt_close($find_stmt);

    if ($row) {
        $invoice_number = $row['invoice_number'];

        $update_txn = mysqli_prepare($conn, "UPDATE mpesa_transactions SET status = ?, result_description = ? WHERE checkout_request_id = ?");
        mysqli_stmt_bind_param($update_txn, "sss", $status, $result_desc, $checkout_request_id);
        mysqli_stmt_execute($update_txn);
        mysqli_stmt_close($update_txn);

        $order_status = ($status === 'completed') ? 'complete' : 'payment_failed';
        $update_order = mysqli_prepare($conn, "UPDATE orders SET order_status = ? WHERE invoice_number = ?");
        mysqli_stmt_bind_param($update_order, "si", $order_status, $invoice_number);
        mysqli_stmt_execute($update_order);
        mysqli_stmt_close($update_order);

        // Decrement stock only now — payment is actually confirmed — and
        // only once per invoice. The stock_quantity >= op.quantity guard
        // stops any row from going negative even under a race condition.
        if ($status === 'completed') {
            $decrement_stmt = mysqli_prepare($conn, "
                UPDATE products p
                JOIN order_pending op ON p.product_id = op.product_id
                SET p.stock_quantity = p.stock_quantity - op.quantity
                WHERE op.invoice_number = ? AND p.stock_quantity >= op.quantity
            ");
            mysqli_stmt_bind_param($decrement_stmt, "i", $invoice_number);
            mysqli_stmt_execute($decrement_stmt);
            mysqli_stmt_close($decrement_stmt);
        }
    }
}

// Safaricom expects a 200 response acknowledging receipt.
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);