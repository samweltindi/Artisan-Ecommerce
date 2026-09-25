<?php
session_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
include('../database_connection.php');
include('../functions/sharedfunctions.php');
include('../includes/mpesa.php'); // existing daraja file
include('../includes/pricing.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$customer_stmt = mysqli_prepare($conn, "select customer_id from customers where username = ?");
mysqli_stmt_bind_param($customer_stmt, "s", $username);
mysqli_stmt_execute($customer_stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($customer_stmt));
mysqli_stmt_close($customer_stmt);
if (!$row) {
    die("Customer not found");
}
$customer_id = $row['customer_id'];

// ---- Two entry points into this page: ----
//  1. Normal checkout from the live cart (no invoice_number in the URL)
//  2. Paying an existing unpaid order, e.g. from "Confirm/Pay Now" on the
//     orders list (?invoice_number=...) — reload that order's items from
//     order_pending instead of the (possibly now-empty) cart.
$existing_invoice = isset($_GET['invoice_number']) ? (int) $_GET['invoice_number'] : 0;
$delivery = $_SESSION['delivery'] ?? null;

if ($existing_invoice) {
    // Make sure this order belongs to the logged-in customer and isn't
    // already paid — customers shouldn't be able to re-trigger payment
    // on someone else's order or one that's already gone through.
    $order_stmt = mysqli_prepare($conn, "SELECT total_amount, subtotal, platform_commission, tax_amount, delivery_fee, artisan_payout, order_status
                                          FROM orders WHERE invoice_number = ? AND customer_id = ?");
    mysqli_stmt_bind_param($order_stmt, "ii", $existing_invoice, $customer_id);
    mysqli_stmt_execute($order_stmt);
    $order_row = mysqli_fetch_assoc(mysqli_stmt_get_result($order_stmt));
    mysqli_stmt_close($order_stmt);

    if (!$order_row) {
        die("Order not found.");
    }
    if ($order_row['order_status'] === 'complete') {
        header("Location: profile.php");
        exit;
    }

    $invoice_number = $existing_invoice;

    // Reuse the breakdown already stored on this order (set the first time
    // it was created) rather than recalculating — rates may change over
    // time and a repay attempt should charge exactly what was quoted.
    $breakdown = [
        'subtotal'       => $order_row['subtotal'],
        'commission'     => $order_row['platform_commission'],
        'tax'            => $order_row['tax_amount'],
        'delivery_fee'   => $order_row['delivery_fee'],
        'grand_total'    => $order_row['total_amount'],
        'artisan_payout' => $order_row['artisan_payout'],
    ];
    $total_price = $breakdown['grand_total'];

    $items_stmt = mysqli_prepare($conn, "SELECT op.product_id, op.quantity, p.product_price, p.product_name
                                          FROM order_pending op
                                          JOIN products p ON op.product_id = p.product_id
                                          WHERE op.invoice_number = ? AND op.customer_id = ?");
    mysqli_stmt_bind_param($items_stmt, "ii", $existing_invoice, $customer_id);
    mysqli_stmt_execute($items_stmt);
    $items_result = mysqli_stmt_get_result($items_stmt);

    $cart_items = [];
    while ($item = mysqli_fetch_assoc($items_result)) {
        $item['line_total'] = $item['product_price'] * $item['quantity'];
        $cart_items[] = $item;
    }
    mysqli_stmt_close($items_stmt);

    if (count($cart_items) === 0) {
        die("No items found for this order.");
    }
} else {
    if (!isset($_SESSION['delivery'])) {
        header("Location: delivery_address.php");
        exit;
    }

    // ---- Pull this customer's cart items, correctly scoped and totalled ----
    $get_ip_address = getIPAddress();
    $cart_stmt = mysqli_prepare($conn, "SELECT c.product_id, c.quantity, p.product_price, p.product_name
                                         FROM cart_items c
                                         JOIN products p ON c.product_id = p.product_id
                                         WHERE c.ip_address = ?");
    mysqli_stmt_bind_param($cart_stmt, "s", $get_ip_address);
    mysqli_stmt_execute($cart_stmt);
    $cart_result = mysqli_stmt_get_result($cart_stmt);

    $cart_items    = [];
    $total_price   = 0;
    $total_products = 0;
    while ($item = mysqli_fetch_assoc($cart_result)) {
        $item['line_total'] = $item['product_price'] * $item['quantity'];
        $total_price += $item['line_total'];
        $total_products++;
        $cart_items[] = $item;
    }
    mysqli_stmt_close($cart_stmt);

    if (count($cart_items) === 0) {
        header("Location: ../cart.php");
        exit;
    }

    // total_price so far is just the product subtotal — derive commission,
    // tax, and delivery from it, and charge the grand total via STK push.
    $breakdown = calculate_order_breakdown($total_price);
    $total_price = $breakdown['grand_total'];
}

$error = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ---- Handle "Pay Now" ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid session. Please refresh and try again.";
    } else {
    $phone = trim($_POST['mpesa_phone'] ?? '');

    if (!preg_match('/^(?:254|0)(7|1)\d{8}$/', $phone)) {
        $error = "Enter a valid M-Pesa phone number.";
    } else {
        $status = 'pending';

        if ($existing_invoice) {
            // Order + order_pending rows already exist from the first
            // checkout attempt — just retry the STK push against them.
            $invoice_number = $existing_invoice;
        } else {
            $invoice_number = mt_rand();

            $insert_order = mysqli_prepare($conn, "INSERT INTO orders
                (customer_id, total_amount, subtotal, platform_commission, tax_amount, delivery_fee, artisan_payout, invoice_number, total_products, order_date, order_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
            mysqli_stmt_bind_param(
                $insert_order,
                "idddddiiis",
                $customer_id,
                $breakdown['grand_total'],
                $breakdown['subtotal'],
                $breakdown['commission'],
                $breakdown['tax'],
                $breakdown['delivery_fee'],
                $breakdown['artisan_payout'],
                $invoice_number,
                $total_products,
                $status
            );
            mysqli_stmt_execute($insert_order);
            mysqli_stmt_close($insert_order);

            // One order_pending row per product (fixes the "only last item saved" issue in order.php).
            $insert_pending = mysqli_prepare($conn, "INSERT INTO order_pending (customer_id, invoice_number, product_id, quantity, order_status)
                                                       VALUES (?, ?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                mysqli_stmt_bind_param($insert_pending, "iiiis", $customer_id, $invoice_number, $item['product_id'], $item['quantity'], $status);
                mysqli_stmt_execute($insert_pending);
            }
            mysqli_stmt_close($insert_pending);
        }

        $stk_response = initiateSTKPush($phone, $total_price, $invoice_number);

        if (!$stk_response['success']) {
            // STK push itself failed (bad creds, network, etc.) — don't
            // silently redirect as if payment is in progress.
            $error = $stk_response['error'] ?? 'Could not initiate M-Pesa payment. Please try again.';
        } else {
            // Record this attempt in mpesa_transactions so the callback
            // (mpesa_callback.php) has a row to match against and update.
            $checkout_request_id = $stk_response['CheckoutRequestID'];
            $insert_txn = mysqli_prepare($conn, "INSERT INTO mpesa_transactions
                                                   (invoice_number, checkout_request_id, phone, amount, status)
                                                   VALUES (?, ?, ?, ?, 'pending')");
            mysqli_stmt_bind_param($insert_txn, "issd", $invoice_number, $checkout_request_id, $phone, $total_price);
            mysqli_stmt_execute($insert_txn);
            mysqli_stmt_close($insert_txn);
            // Get artisan name from the first product's artisan
$artisan_name = 'ArtisanOrders';
$pid = (int) $cart_items[0]['product_id'];
$a_stmt = mysqli_prepare($conn, "SELECT a.full_name FROM products p
                                 JOIN artisans a ON a.artisan_id = p.artisan_id
                                 WHERE p.product_id = ? LIMIT 1");
mysqli_stmt_bind_param($a_stmt, "i", $pid);
mysqli_stmt_execute($a_stmt);
$a_row = mysqli_fetch_assoc(mysqli_stmt_get_result($a_stmt));
mysqli_stmt_close($a_stmt);
if ($a_row && !empty($a_row['full_name'])) {
    $artisan_name = $a_row['full_name'];
}

// Save delivery details (only for new checkouts with session delivery)
if (!empty($delivery)) {
    $city = !empty($delivery['town']) ? $delivery['town'] : ($delivery['county'] ?? '');
    $notes = $delivery['notes'] ?? '';

    $insert_delivery = mysqli_prepare($conn, "INSERT INTO deliveries
        (invoice_number, customer_id, recipient_name, phone_number,
         delivery_address, city, delivery_fee, delivery_status,
         courier_name, tracking_notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
    mysqli_stmt_bind_param($insert_delivery, "iissssdss",
        $invoice_number,
        $customer_id,
        $delivery['full_name'],
        $delivery['phone'],
        $delivery['address'],
        $city,
        $breakdown['delivery_fee'],
        $artisan_name,
        $notes
    );
    mysqli_stmt_execute($insert_delivery);
    mysqli_stmt_close($insert_delivery);
}
// ===== END OF ADDED BLOCK =====

if (!$existing_invoice) {
    // Clear the cart now that the order + transaction are recorded.
    $empty_cart = mysqli_prepare($conn, "DELETE FROM cart_items WHERE ip_address = ?");
    mysqli_stmt_bind_param($empty_cart, "s", $get_ip_address);
    mysqli_stmt_execute($empty_cart);
    mysqli_stmt_close($empty_cart);

    unset($_SESSION['delivery']);
}

header("Location: payment_status.php?invoice_number=" . urlencode($invoice_number));
exit;

            if (!$existing_invoice) {
                // Clear the cart now that the order + transaction are recorded.
                $empty_cart = mysqli_prepare($conn, "DELETE FROM cart_items WHERE ip_address = ?");
                mysqli_stmt_bind_param($empty_cart, "s", $get_ip_address);
                mysqli_stmt_execute($empty_cart);
                mysqli_stmt_close($empty_cart);

                unset($_SESSION['delivery']);
            }

            // Send them to a page that polls mpesa_transactions.status for
            // this invoice, NOT straight to profile.php — payment hasn't
            // actually been confirmed yet at this point.
            header("Location: payment_status.php?invoice_number=" . urlencode($invoice_number));
            exit;
        }
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay with M-Pesa</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../style.css">
    <style>
         body{background: #E8F0F3}
        .manage{ display: flex; background: white; align-items: center; justify-content: center; padding: 15px; }
        .row123{ display: flex; padding: 15px; align-items: center; list-style-type: none; gap: 20px; margin-top: 10px; margin-bottom: 10px; background-color: #f8f9fa; }
        .row123 li{ display : inline; }
        .row123 a{ text-decoration: none; color: black; padding: 10px 15px; border-radius: 4px; }
        .btn-artisan{ display: inline-block; padding: 12px 24px; font-size: 16px; font-weight: 600; text-align: center; text-decoration: none; border: none; cursor: pointer; color: white; background-color:#35c7c9; border-radius:4px; }
        .profile_title{ background-color: #35c7c9; }
        .mpesa-box{ max-width: 480px; margin: 20px auto; background:#f8f9fa; padding:24px; border-radius:6px; }
        .mpesa-box .form-group{ margin-bottom:16px; }
        .mpesa-box label{ display:block; font-weight:600; margin-bottom:6px; font-size:0.9rem; }
        .mpesa-box input{ width:100%; padding:10px; border:1px solid #dee2e6; border-radius:4px; box-sizing:border-box; }
        .order-line{ display:flex; justify-content:space-between; font-size:0.9rem; padding:4px 0; }
        .order-total{ display:flex; justify-content:space-between; font-weight:700; border-top:1px solid #dee2e6; margin-top:8px; padding-top:10px; }
        .error-box{ background:#fdeceb; border:1px solid #f3c6c2; color:#c1443a; padding:12px 16px; border-radius:6px; margin-bottom:16px; font-size:0.88rem; max-width:480px; margin-left:auto; margin-right:auto; }
        .nav-item{list-style: none;}
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo"><img src="../logo1.svg" alt="logo"/></div>
            <li class="nav-item">Welcome  <?php echo htmlspecialchars($username); ?></li>
    </header>
    </div>

    <div class="container">
        <h2 class="profile_title" style="text-align:center; padding:10px;">Pay with M-Pesa</h2>

        <?php if ($error): ?>
            <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="mpesa-box">
            <?php if ($delivery): ?>
            <p style="font-size:0.85rem;color:#5a6b82;margin-bottom:14px;">
                Delivering to <strong><?php echo htmlspecialchars($delivery['full_name']); ?></strong>,
                <?php echo htmlspecialchars($delivery['address']); ?>, <?php echo htmlspecialchars($delivery['town']); ?>, <?php echo htmlspecialchars($delivery['county']); ?>
                &nbsp;<a href="delivery_address.php">Edit</a>
            </p>
            <?php elseif ($existing_invoice): ?>
            <p style="font-size:0.85rem;color:#5a6b82;margin-bottom:14px;">
                Completing payment for order #<?php echo (int) $invoice_number; ?>
            </p>
            <?php endif; ?>

            <?php foreach ($cart_items as $item): ?>
                <div class="order-line">
                    <span><?php echo htmlspecialchars($item['product_name']); ?> &times; <?php echo (int) $item['quantity']; ?></span>
                    <span>Ksh <?php echo number_format($item['line_total'], 2); ?></span>
                </div>
            <?php endforeach; ?>
            <div class="order-line">
                <span>Subtotal</span>
                <span>Ksh <?php echo number_format($breakdown['subtotal'], 2); ?></span>
            </div>
            <div class="order-line">
                <span>Tax (VAT)</span>
                <span>Ksh <?php echo number_format($breakdown['tax'], 2); ?></span>
            </div>
            <div class="order-line">
                <span>Delivery fee</span>
                <span>Ksh <?php echo number_format($breakdown['delivery_fee'], 2); ?></span>
            </div>
            <div class="order-total">
                <span>Total</span>
                <span>Ksh <?php echo number_format($breakdown['grand_total'], 2); ?></span>
            </div>

            <form method="POST" action="mpesa_payment.php?customer_id=<?php echo (int) $customer_id; ?><?php echo $existing_invoice ? '&invoice_number=' . (int) $existing_invoice : ''; ?>" style="margin-top:18px;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"/>
                <div class="form-group">
                    <label for="mpesa_phone">M-Pesa Phone Number</label>
                    <input type="tel" id="mpesa_phone" name="mpesa_phone" placeholder="07XXXXXXXX" value="<?php echo htmlspecialchars($delivery['phone'] ?? ''); ?>" required>
                </div>
                <button type="submit" name="pay_now" class="btn-artisan" style="width:100%;">Pay Ksh <?php echo number_format($breakdown['grand_total'], 2); ?></button>
            </form>
        </div>
    </div>

    <footer>
        <div class="footerdiv">
            <div>
                <h4>Contact us</h4>
                <p>Email: Samweltindi07@gmail.com<br>Phone: 0742086326</p>
            </div>
            <div>
                <h4>Follow us</h4>
                <span><i class="fa-brands fa-square-facebook"></i> | <i class="fa-brands fa-x-twitter"></i> | <i class="fa-brands fa-whatsapp"></i> | <i class="fa-brands fa-instagram"></i></span>
            </div>
        </div>
        <p class="footerP">Copyright &copy; 2026 ArtisanOrders, All rights reserved.</p>
    </footer>
</body>
</html>