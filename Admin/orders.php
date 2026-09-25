<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//must login as admin block everyone else
if(empty($_SESSION['role']) || $_SESSION['role'] !== 'admin')
            {
                echo "<script>alert('Access denied. ')</script>";
                echo "<script>window.location.href='../Artisans/login.php';</script>";
                exit;
            }
?>
<style>
.products-card{
    max-width: 1100px;
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
.status-pending{
    background-color: #fdf1e0;
    color: #b3701a;
}
</style>

<div class="products-card">
<h3>All Orders</h3>
<?php
$get_orders="select * from orders";
$result = mysqli_query($conn,$get_orders);
$rows_count = mysqli_num_rows($result);

if($rows_count == 0)
    {
        echo "<p style='text-align:center; padding:1rem;'>No orders yet.</p>";
    }else{
?>
<table class="table1">
    <thead>
        <tr>
            <th>Serial number</th>
            <th>Amount due</th>
            <th>Invoice number</th>
            <th>Total Products</th>
            <th>Order Date</th>
            <th>Status</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $number = 0;
        while($row_data=mysqli_fetch_assoc($result)){
            $order_id = $row_data['order_id'];
            $amount_due = $row_data['total_amount'];
            $invoice_number = $row_data['invoice_number'];
            $total_products = $row_data['total_products'];
            $order_date = $row_data['order_date'];
            $order_status = $row_data['order_status'];
            $status_class = ($order_status === 'complete') ? 'status-complete' : 'status-pending';
            $number++;
        ?>
        <tr>
            <td><?php echo (int) $number; ?></td>
            <td>Ksh <?php echo number_format((float) $amount_due, 2); ?></td>
            <td><?php echo htmlspecialchars($invoice_number); ?></td>
            <td><?php echo (int) $total_products; ?></td>
            <td><?php echo htmlspecialchars($order_date); ?></td>
            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($order_status); ?></span></td>
            <td><a href="./index.php?delete_orders=<?php echo (int) $order_id; ?>" title="Delete" onclick="return confirm('Delete this order?');"><i class="fa-solid fa-trash"></i></a></td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>
<?php } ?>
</div>