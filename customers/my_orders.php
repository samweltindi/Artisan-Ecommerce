<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <style>
    .orders-card{
        max-width: 900px;
        margin: 20px auto;
        padding: 40px;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #eef1f4;
    }
    .orders-card h3{
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
        margin-top: 0;
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
    .table1 a{
        color: #0cc012;
        font-weight: 600;
        text-decoration: none;
    }
    .table1 a:hover{
        text-decoration: underline;
    }
    .status-badge{
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
    }
    .status-complete{
        background-color: #e6f7e8;
        color: #1a8a24;
    }
    .status-incomplete{
        background-color: #fdf1e0;
        color: #b3701a;
    }
    .action-delivered{
        color: #1a8a24;
        font-weight: 600;
    }
    </style>
</head>
<body>
    <?php
    // Must be logged in 
    if (empty($_SESSION['username'])) {
        echo "<script>alert('Please log in first.')</script>";
        echo "<script>window.open('login.php','_self')</script>";
        exit;
    }
    $username = $_SESSION['username'];
    $user_stmt = mysqli_prepare($conn, "SELECT customer_id FROM customers WHERE username = ?");
    mysqli_stmt_bind_param($user_stmt, "s", $username);
    mysqli_stmt_execute($user_stmt);
    $row_fetch = mysqli_fetch_assoc(mysqli_stmt_get_result($user_stmt));
    mysqli_stmt_close($user_stmt);
    if (!$row_fetch) {
        echo "<script>alert('Account not found.')</script>";
        echo "<script>window.open('logout.php','_self')</script>";
        exit;
    }
    $user_id = $row_fetch['customer_id'];
    ?>
    <div class="orders-card">
    <h3>All Orders</h3>
    <table class="table1">
        <thead>
            <tr>
                <th>Serial no</th>
                <th>Total Amount</th>
                <th>Total products</th>
                <th>Order Date</th>
                <th>Order Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody class="bg-secondary">
            <?php
            $order_stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE customer_id = ?");
            mysqli_stmt_bind_param($order_stmt, "i", $user_id);
            mysqli_stmt_execute($order_stmt);
            $result_orders = mysqli_stmt_get_result($order_stmt);

            $number = 1;
            while ($row_orders = mysqli_fetch_assoc($result_orders)) {
                $invoice_number   = $row_orders['invoice_number'];
                $amount_due       = $row_orders['total_amount'];
                $total_products   = $row_orders['total_products'];
                $order_date       = $row_orders['order_date'];
                $raw_status       = $row_orders['order_status']; // 'pending' or 'complete'
                $delivery_confirmed = !empty($row_orders['delivery_confirmed_at']);

                $label = ($raw_status === 'complete') ? 'complete' : 'incomplete';
                $badge_class = ($raw_status === 'complete') ? 'status-complete' : 'status-incomplete';

                echo "<tr>
                        <td>".(int)$number."</td>
                        <td>Ksh " . number_format($amount_due, 2) . "</td>
                        <td>".(int)$total_products."</td>
                        <td>".htmlspecialchars($order_date)."</td>
                        <td><span class='status-badge $badge_class'>".htmlspecialchars($label)."</span></td>";

                if ($raw_status !== 'complete') {
                    // Not paid yet — same as before.
                    echo "<td><a href='mpesa_payment.php?invoice_number=".(int)$invoice_number."'>Pay Now</a></td>";
                } elseif ($delivery_confirmed) {
                    echo "<td><span class='action-delivered'>Delivered</span></td>";
                } else {
                    echo "<td><a href='confirm_delivery.php?invoice_number=".(int)$invoice_number."'>Confirm Delivery</a></td>";
                }

                echo "</tr>";
                $number++;
            }
            mysqli_stmt_close($order_stmt);
            ?>
        </tbody>
    </table>
    </div>
</body>
</html>