
<?php
//this file fetches delivery information.
if(isset($_GET['delivery']))
    {
        // Must be logged in - block access otherwise
        if (empty($_SESSION['username'])) {
            echo "<script>alert('Please log in first.')</script>";
            echo "<script>window.open('login.php','_self')</script>";
            exit;
        }

        $customer_session = $_SESSION['username'];

        // Look up the logged-in customer's ID via their session username
        $customer_query = "select customer_id from customers where username = ?";
        $customer_stmt = mysqli_prepare($conn, $customer_query);
        mysqli_stmt_bind_param($customer_stmt, "s", $customer_session);
        mysqli_stmt_execute($customer_stmt);
        $customer_result = mysqli_stmt_get_result($customer_stmt);
        $customer_row = mysqli_fetch_assoc($customer_result);

        if (!$customer_row) {
            echo "<script>alert('Account not found.')</script>";
            echo "<script>window.open('logout.php','_self')</script>";
            exit;
        }

        $customer_id = $customer_row['customer_id'];

        // Fetch every delivery record tied to this customer, most recent first
        $delivery_query = "select * from deliveries where customer_id = ? order by created_at desc";
        $delivery_stmt = mysqli_prepare($conn, $delivery_query);
        mysqli_stmt_bind_param($delivery_stmt, "i", $customer_id);
        mysqli_stmt_execute($delivery_stmt);
        $delivery_result = mysqli_stmt_get_result($delivery_stmt);
    }

// Maps each status to a display label and a CSS class - keeps HTML below clean
function delivery_status_badge($status) {
    $map = [
        'pending'     => ['label' => 'Pending',     'class' => 'del-status-pending'],
        'dispatched'  => ['label' => 'Dispatched',  'class' => 'del-status-dispatched'],
        'in_transit'  => ['label' => 'In Transit',  'class' => 'del-status-transit'],
        'delivered'   => ['label' => 'Delivered',   'class' => 'del-status-delivered'],
        'failed'      => ['label' => 'Failed',      'class' => 'del-status-failed'],
    ];
    return $map[$status] ?? ['label' => htmlspecialchars($status), 'class' => 'del-status-pending'];
}
?>
<style>
.del-wrap{
    background-color: white;
    padding: 20px;
    margin-top: 20px;

}
.del-wrap h3{
    color: green;
    margin-bottom: 20px;
}
.del-card{
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
    background-color: #f8f9fa;
}
.del-card-top{
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.del-invoice{
    font-weight: 600;
    font-size: 15px;
}
.del-detail{
    font-size: 14px;
    color: #444;
    margin-bottom: 4px;
}
.del-detail span{
    font-weight: 600;
    color: #222;
}
.del-status{
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}
.del-status-pending{ background: #fff3cd; color: #7a5b00; }
.del-status-dispatched{ background: #d6ebff; color: #0a4a8a; }
.del-status-transit{ background: #e0d6ff; color: #4a1a8a; }
.del-status-delivered{ background: #d4f8dc; color: #0E4D3C; }
.del-status-failed{ background: #fbdada; color: #a11; }
.del-empty{
    text-align: center;
    padding: 30px;
    color: #777;
}
</style>
<div class="del-wrap">
    <h3>My Deliveries</h3>

    <?php if (isset($delivery_result) && mysqli_num_rows($delivery_result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($delivery_result)): ?>
            <?php $badge = delivery_status_badge($row['delivery_status']); ?>
            <div class="del-card">
                <div class="del-card-top">
                    <span class="del-invoice">Invoice #<?php echo htmlspecialchars($row['invoice_number']); ?></span>
                    <span class="del-status <?php echo $badge['class']; ?>"><?php echo $badge['label']; ?></span>
                </div>
                <div class="del-detail"><span>Recipient:</span> <?php echo htmlspecialchars($row['recipient_name']); ?></div>
                <div class="del-detail"><span>Phone:</span> <?php echo htmlspecialchars($row['phone_number']); ?></div>
                <div class="del-detail"><span>Address:</span> <?php echo htmlspecialchars($row['delivery_address']); ?><?php echo $row['city'] ? ', ' . htmlspecialchars($row['city']) : ''; ?></div>
                <div class="del-detail"><span>Delivery Fee:</span> KES <?php echo htmlspecialchars(number_format((float)$row['delivery_fee'], 2)); ?></div>
                <?php if (!empty($row['courier_name'])): ?>
                    <div class="del-detail"><span>Courier:</span> <?php echo htmlspecialchars($row['courier_name']); ?></div>
                <?php endif; ?>
                <?php if (!empty($row['tracking_notes'])): ?>
                    <div class="del-detail"><span>Notes:</span> <?php echo htmlspecialchars($row['tracking_notes']); ?></div>
                <?php endif; ?>
                <?php if (!empty($row['dispatched_at'])): ?>
                    <div class="del-detail"><span>Dispatched:</span> <?php echo htmlspecialchars($row['dispatched_at']); ?></div>
                <?php endif; ?>
                <?php if (!empty($row['delivered_at'])): ?>
                    <div class="del-detail"><span>Delivered:</span> <?php echo htmlspecialchars($row['delivered_at']); ?></div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="del-empty">No deliveries found yet.</div>
    <?php endif; ?>
</div>