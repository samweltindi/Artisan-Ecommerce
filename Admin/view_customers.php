<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// This page creates admin accounts - it must never be reachable by anyone only registered admin
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Access denied.')</script>";
    echo "<script>window.location.href='../Artisans/login.php';</script>";
    exit;
}
if(empty($_SESSION['csrf_token']))
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
?>
<style>
.products-card{
    max-width: 1200px;
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
.table1 a{
    margin-right: 10px;
}
.status-badge{
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}
.status-active{
    background-color: #e6f7e8;
    color: #1a8a24;
}
.status-suspended{
    background-color: #fdeaea;
    color: #a11;
}
</style>

<div class="products-card">
<h3>Customers</h3>
<?php
$get_users="select * from customers";
$result = mysqli_query($conn,$get_users);
$rows_count = mysqli_num_rows($result);

if($rows_count == 0)
    {
        echo "<p style='text-align:center; padding:1rem;'>No customers yet.</p>";
    }else{
?>
<table class="table1">
    <thead>
        <tr>
            <th>Serial number</th>
            <th>Username</th>
            <th>Email</th>
            <th>User address</th>
            <th>Status</th>
            <th>Mobile number</th>
            <th>Update</th>
            <th>Suspend</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $number = 0;
        while($row_data=mysqli_fetch_assoc($result)){
            $customer_id = $row_data['customer_id'];
            $username = $row_data['username'];
            $email = $row_data['email'];
            $user_address = $row_data['address'];
            $customer_status = $row_data['status'];
            $phone = $row_data['phone_number'];
            $suspend_icon = ($customer_status === 'active') ? 'fa-ban' : 'fa-rotate-left';
            $suspend_title = ($customer_status === 'active') ? 'Suspend' : 'Unsuspend';
            $status_class = 'status-' . strtolower(htmlspecialchars($customer_status));
            $number++;
        ?>
        <tr>
            <td><?php echo (int) $number; ?></td>
            <td><?php echo htmlspecialchars($username); ?></td>
            <td><?php echo htmlspecialchars($email); ?></td>
            <td><?php echo htmlspecialchars($user_address); ?></td>
            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($customer_status); ?></span></td>
            <td><?php echo htmlspecialchars($phone); ?></td>
            <td><a href="index.php?edit_customers=<?php echo (int) $customer_id; ?>" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a></td>
            <td><a href="./index.php?suspend_customer=<?php echo (int) $customer_id; ?>" title="<?php echo htmlspecialchars($suspend_title); ?>" onclick="return confirm('<?php echo htmlspecialchars($suspend_title); ?> this customer?');"><i class="fa-solid <?php echo $suspend_icon; ?>"></i></a></td>
            <td><a href="./index.php?delete_customer=<?php echo (int) $customer_id; ?>" title="Delete" onclick="return confirm('Delete this customer?');"><i class="fa-solid fa-trash"></i></a></td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>
<?php } ?>
</div>