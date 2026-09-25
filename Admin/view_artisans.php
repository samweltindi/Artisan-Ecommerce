<?php 
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
<h3>Artisans</h3>
<?php
$get_users="select * from artisans";
$result = mysqli_query($conn,$get_users);
$rows_count = mysqli_num_rows($result);

if($rows_count == 0)
    {
        echo "<p style='text-align:center; padding:1rem;'>No artisans yet.</p>";
    }else{
?>
<table class="table1">
    <thead>
        <tr>
            <th>Serial number</th>
            <th>Username</th>
            <th>Email</th>
            <th>Mobile number</th>
            <th>Location</th>
            <th>Status</th>
            <th>ID Number</th>
            <th>Approve</th>
            <th>Suspend</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $number = 0;
        while($row_data=mysqli_fetch_assoc($result)){
            $artisan_id = $row_data['artisan_id'];
            $username = $row_data['username'];
            $email = $row_data['email'];
            $phone = $row_data['phone_number'];
            $location = $row_data['location'];
            $idno = $row_data['id_number'];
            $artisan_status = $row_data['status'];
            $approved = $row_data['approved'];
            $approve_icon = ($approved == 1) ? 'fa-circle-check' : 'fa-user-check';
            $approve_color = ($approved == 1) ? '#1a8a24' : '#6b7280';
            $approve_title = ($approved == 1) ? 'Approved' : 'Approve';
            $suspend_icon = ($artisan_status === 'active') ? 'fa-ban' : 'fa-rotate-left';
            $suspend_title = ($artisan_status === 'active') ? 'Suspend' : 'Unsuspend';
            $status_class = 'status-' . strtolower(htmlspecialchars($artisan_status));
            $number++;
        ?>
        <tr>
            <td><?php echo (int) $number; ?></td>
            <td><?php echo htmlspecialchars($username); ?></td>
            <td><?php echo htmlspecialchars($email); ?></td>
            <td><?php echo htmlspecialchars($phone); ?></td>
            <td><?php echo htmlspecialchars($location); ?></td>
            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($artisan_status); ?></span></td>
            <td><?php echo htmlspecialchars($idno); ?></td>
            <td><a href="./index.php?review_artisan=<?php echo (int) $artisan_id; ?>" title="<?php echo htmlspecialchars($approve_title); ?>"><i class="fa-solid <?php echo $approve_icon; ?>" style="color:<?php echo $approve_color; ?>"></i></a></td>
            <td><a href="./index.php?suspend_artisan=<?php echo (int) $artisan_id; ?>" title="<?php echo htmlspecialchars($suspend_title); ?>" onclick="return confirm('<?php echo htmlspecialchars($suspend_title); ?> this artisan?');"><i class="fa-solid <?php echo $suspend_icon; ?>"></i></a></td>
            <td><a href="./index.php?delete_artisan=<?php echo (int) $artisan_id; ?>" title="Delete" onclick="return confirm('Delete this artisan?');"><i class="fa-solid fa-trash"></i></a></td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>
<?php } ?>
</div>