<?php

// Must be logged in - block access otherwise
if (empty($_SESSION['username'])) {
    echo "<script>alert('Please log in first.')</script>";
    echo "<script>window.open('login.php','_self')</script>";
    exit;
}

$customer_session = $_SESSION['username'];
$select_query = "select * from customers where username = ?";
$stmt = mysqli_prepare($conn, $select_query);
mysqli_stmt_bind_param($stmt, "s", $customer_session);
mysqli_stmt_execute($stmt);
$result_query = mysqli_stmt_get_result($stmt);
$row_fetch = mysqli_fetch_assoc($result_query);

// Bail out if the session username no longer matches a real account
if (!$row_fetch) {
    echo "<script>alert('Account not found.')</script>";
    echo "<script>window.open('logout.php','_self')</script>";
    exit;
}

$first_name  = $row_fetch['first_name'];
$last_name   = $row_fetch['last_name'];
$user_name   = $row_fetch['username'];
$user_email  = $row_fetch['email'];
$address     = $row_fetch['address'];
$phone       = $row_fetch['phone_number'];
$status      = $row_fetch['status'] ?? 'active';
?>
<style>
.acct-wrap{
    background-color: white;
    padding: 20px;
    margin-bottom: 20px;
    margin-top: 20px;
    border-radius: 8px;
}
.acct-wrap h3{
    color: green;
    margin-bottom: 20px;
}
.acct-row{
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    margin-bottom: 12px;
    background-color: #f8f9fa;
}
.acct-label{
    font-weight: 600;
    font-size: 14px;
    color: #555;
}
.acct-value{
    font-size: 15px;
    color: #222;
    text-align: right;
    word-break: break-word;
}
.acct-status{
    display: inline-block;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    text-transform: capitalize;
}
.acct-status-active{
    background: #d4f8dc;
    color: #0E4D3C;
}
.acct-status-suspended{
    background: #fbdada;
    color: #a11;
}
.acct-edit-link{
    display: inline-block;
    margin-top: 10px;
    padding: 10px 20px;
    background: #0E4D3C;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
}
</style>
<div class="acct-wrap">
    <h3>Account Details</h3>

    <div class="acct-row">
        <span class="acct-label">First Name</span>
        <span class="acct-value"><?php echo htmlspecialchars($first_name); ?></span>
    </div>

    <div class="acct-row">
        <span class="acct-label">Last Name</span>
        <span class="acct-value"><?php echo htmlspecialchars($last_name); ?></span>
    </div>

    <div class="acct-row">
        <span class="acct-label">Username</span>
        <span class="acct-value"><?php echo htmlspecialchars($user_name); ?></span>
    </div>

    <div class="acct-row">
        <span class="acct-label">Email</span>
        <span class="acct-value"><?php echo htmlspecialchars($user_email); ?></span>
    </div>

    <div class="acct-row">
        <span class="acct-label">Phone Number</span>
        <span class="acct-value"><?php echo htmlspecialchars($phone); ?></span>
    </div>

    <div class="acct-row">
        <span class="acct-label">Address</span>
        <span class="acct-value"><?php echo htmlspecialchars($address); ?></span>
    </div>

    <div class="acct-row">
        <span class="acct-label">Account Status</span>
        <span class="acct-value">
            <span class="acct-status <?php echo 
            $status === 'suspended' ? 'acct-status-suspended' : 'acct-status-active'; ?>">
                <?php echo htmlspecialchars($status); ?>
            </span>
        </span>
    </div>

    <a href="profile.php?edit_account" class="acct-edit-link">Edit Account</a>
</div>