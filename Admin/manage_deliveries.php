<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(isset($_GET['manage_deliveries']))
    {
        // Must be logged in as an admin - block everyone else
        if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo "<script>alert('Access denied.')</script>";
            echo "<script>window.location.href='../Artisans/login.php';</script>";
            exit;
        }

        // CSRF token
       csrf_token();

        // Only these values are ever accepted for delivery_status - whitelist,
        // never trust the raw POST value directly in the query
        $allowed_statuses = ['pending', 'dispatched', 'in_transit', 'delivered', 'failed'];

        // ---- Handle status/courier/notes update ----
        if (isset($_POST['update_delivery'])) {
            if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                echo "<script>alert('Invalid session. Please refresh and try again.')</script>";
                exit;
            }

            $delivery_id = (int)($_POST['delivery_id'] ?? 0);
            $new_status = $_POST['delivery_status'] ?? '';
            $courier_name = trim($_POST['courier_name'] ?? '');
            $tracking_notes = trim($_POST['tracking_notes'] ?? '');

            $errors = [];

            if ($delivery_id <= 0) {
                $errors[] = 'Invalid delivery record.';
            }
            if (!in_array($new_status, $allowed_statuses, true)) {
                $errors[] = 'Invalid delivery status.';
            }
            if (strlen($courier_name) > 100 || strlen($tracking_notes) > 255) {
                $errors[] = 'Courier name or notes exceed allowed length.';
            }

            if (empty($errors)) {
                // Fetch current row first so we know whether to stamp dispatched_at/delivered_at
                $current_query = "select delivery_status, dispatched_at, delivered_at from deliveries where delivery_id = ?";
                $current_stmt = mysqli_prepare($conn, $current_query);
                mysqli_stmt_bind_param($current_stmt, "i", $delivery_id);
                mysqli_stmt_execute($current_stmt);
                $current_result = mysqli_stmt_get_result($current_stmt);
                $current_row = mysqli_fetch_assoc($current_result);

                if (!$current_row) {
                    echo "<script>alert('Delivery record not found.')</script>";
                } else {
                    // Timestamps are set server-side based on the new status,
                    // never taken directly from client input
                    $dispatched_at = $current_row['dispatched_at'];
                    $delivered_at = $current_row['delivered_at'];

                    if ($new_status === 'dispatched' && empty($dispatched_at)) {
                        $dispatched_at = date('Y-m-d H:i:s');
                    }
                    if ($new_status === 'delivered' && empty($delivered_at)) {
                        $delivered_at = date('Y-m-d H:i:s');
                    }

                    $update_query = "update deliveries set delivery_status = ?, courier_name = ?, tracking_notes = ?, dispatched_at = ?, delivered_at = ? where delivery_id = ?";
                    $update_stmt = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param(
                        $update_stmt,
                        "sssssi",
                        $new_status,
                        $courier_name,
                        $tracking_notes,
                        $dispatched_at,
                        $delivered_at,
                        $delivery_id
                    );
                    $update_ok = mysqli_stmt_execute($update_stmt);

                    if ($update_ok) {
                        echo "<script>alert('Delivery updated successfully.')</script>";
                        echo "<script>window.location.href='index.php?manage_deliveries';</script>";
                        exit;
                    } else {
                        echo "<script>alert('Update failed. Please try again.')</script>";
                    }
                }
            } else {
                echo "<script>alert('" . addslashes(implode(' ', $errors)) . "')</script>";
            }
        }

        // ---- If editing a single delivery, fetch it for the edit form ----
        $editing_delivery = null;
        if (isset($_GET['edit_delivery_id'])) {
            $edit_id = (int)$_GET['edit_delivery_id'];
            $edit_query = "select d.*, c.first_name, c.last_name, o.total_amount, o.order_status
                           from deliveries d
                           join customers c on c.customer_id = d.customer_id
                           join orders o on o.invoice_number = d.invoice_number and o.customer_id = d.customer_id
                           where d.delivery_id = ?";
            $edit_stmt = mysqli_prepare($conn, $edit_query);
            mysqli_stmt_bind_param($edit_stmt, "i", $edit_id);
            mysqli_stmt_execute($edit_stmt);
            $edit_result = mysqli_stmt_get_result($edit_stmt);
            $editing_delivery = mysqli_fetch_assoc($edit_result);
        }

        // ---- Full list of deliveries with customer name and order status attached ----
        $list_query = "select d.*, c.first_name, c.last_name, o.total_amount, o.order_status
                       from deliveries d
                       join customers c on c.customer_id = d.customer_id
                       join orders o on o.invoice_number = d.invoice_number and o.customer_id = d.customer_id
                       order by d.created_at desc";
        $list_result = mysqli_query($conn, $list_query);
    }
?>
<style>
.products-card{ background: #ffffff; padding: 40px; max-width: 1200px; margin: 20px auto; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #eef1f4; }
.products-card h3{ color: #1c2b3a; font-size: 1.5rem; font-weight: 700; border-bottom: 1px solid #eef1f4; padding-bottom: 16px; margin: 0 0 24px 0; }
.table1{ width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 14px; }
.table1 th, .table1 td{ padding: 12px; border: none; border-bottom: 1px solid #eef1f4; text-align: left; }
.table1 thead th{ background: #f8f9fa; color: #4a5568; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.03em; }
.table1 tbody tr:hover{ background-color: #fafbfc; }
.mdel-status{ padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.mdel-status-pending{ background:#fdf1e0; color:#b3701a; }
.mdel-status-dispatched{ background:#d6ebff; color:#0a4a8a; }
.mdel-status-in_transit{ background:#e0d6ff; color:#4a1a8a; }
.mdel-status-delivered{ background:#e6f7e8; color:#1a8a24; }
.mdel-status-failed{ background:#fdeaea; color:#a11; }
.mdel-edit-link{ color: #0cc012; font-weight: 600; text-decoration: none; }
.mdel-edit-link:hover{ text-decoration: underline; }
.mdel-form{ border: 1px solid #eef1f4; border-radius: 10px; padding: 24px; max-width: 500px; margin-bottom: 30px; background: #fbfbfc; }
.mdel-form label{ display:block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #4a5568; }
.mdel-form input, .mdel-form select, .mdel-form textarea{ width: 100%; padding: 10px 12px; margin-bottom: 16px; border: 1px solid #dfe3e8; border-radius: 8px; font-size: 14px; }
.mdel-form button{ background: #0cc012; color: white; border: none; padding: 10px 22px; border-radius: 8px; cursor: pointer; font-weight: 600; }
.mdel-form button:hover{ background: #0aa60f; }
</style>
<div class="products-card">
    <h3>Manage Deliveries</h3>

    <?php if ($editing_delivery): ?>
        <form class="mdel-form" method="post" action="index.php?manage_deliveries">
            <?php csrf_field();?>
            <input type="hidden" name="delivery_id" value="<?php echo (int)$editing_delivery['delivery_id']; ?>"/>

            <label>Invoice #<?php echo htmlspecialchars($editing_delivery['invoice_number']); ?> &mdash;
                <?php echo htmlspecialchars($editing_delivery['first_name'] . ' ' . $editing_delivery['last_name']); ?></label>
            <p style="font-size:13px;color:#555;margin-top:-8px;margin-bottom:16px;">
                Payment: <?php echo htmlspecialchars(ucfirst($editing_delivery['order_status'])); ?>
                &mdash; KES <?php echo htmlspecialchars(number_format((float)$editing_delivery['total_amount'], 2)); ?>
            </p>

            <label for="delivery_status">Status</label>
            <select name="delivery_status" id="delivery_status">
                <?php foreach ($allowed_statuses as $status_option): ?>
                    <option value="<?php echo $status_option; ?>" <?php echo $editing_delivery['delivery_status'] === $status_option ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $status_option))); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="courier_name">Courier Name</label>
            <input type="text" name="courier_name" id="courier_name" maxlength="100" value="<?php echo htmlspecialchars($editing_delivery['courier_name'] ?? ''); ?>"/>

            <label for="tracking_notes">Tracking Notes</label>
            <textarea name="tracking_notes" id="tracking_notes" maxlength="255" rows="3"><?php echo htmlspecialchars($editing_delivery['tracking_notes'] ?? ''); ?></textarea>

            <button type="submit" name="update_delivery">Save Update</button>
        </form>
    <?php endif; ?>

    <table class="table1">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Address</th>
                <th>Fee</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($list_result && mysqli_num_rows($list_result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($list_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['invoice_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['delivery_address']); ?></td>
                        <td>KES <?php echo htmlspecialchars(number_format((float)$row['delivery_fee'], 2)); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($row['order_status'])); ?> <br><small>KES <?php echo htmlspecialchars(number_format((float)$row['total_amount'], 2)); ?></small></td>
                        <td><span class="mdel-status mdel-status-<?php echo htmlspecialchars($row['delivery_status']); ?>">
                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $row['delivery_status']))); ?>
                        </span></td>
                        <td><a class="mdel-edit-link" href="index.php?manage_deliveries&edit_delivery_id=<?php echo (int)$row['delivery_id']; ?>">Manage</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;">No deliveries found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>