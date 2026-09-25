
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
.table1 a{
    color: #0cc012;
    font-weight: 600;
    text-decoration: none;
    margin-right: 10px;
}
.table1 a:hover{
    text-decoration: underline;
}
.product-thumb{
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #eef1f4;
}
.status-badge{
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}
.status-approved, .status-active{
    background-color: #e6f7e8;
    color: #1a8a24;
}
.status-pending{
    background-color: #fdf1e0;
    color: #b3701a;
}
.status-rejected, .status-inactive{
    background-color: #fdeaea;
    color: #a11;
}
</style>

<div class="products-card">
<h3>My Products</h3>
<?php
// Look up the logged-in artisan's own artisan_id — assumes session_start()
// and database_connection.php were already included by index.php.
$username = $_SESSION['username'] ?? null;
$artisan_id = null;

if ($username) {
    //getting artisan id
    $get_artisanID = mysqli_prepare($conn, "SELECT artisan_id FROM artisans WHERE username = ?");
    mysqli_stmt_bind_param($get_artisanID, "s", $username);
    mysqli_stmt_execute($get_artisanID);
    $artisan_result = mysqli_stmt_get_result($get_artisanID);
    $artisan_row = mysqli_fetch_assoc($artisan_result);
    mysqli_stmt_close($get_artisanID);
    $artisan_id = $artisan_row['artisan_id'] ?? null;
}
?>
<table class="table1">
    <thead>
        <tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Product Image</th>
            <th>Product Price</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Approval Status</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $number = 0;
        if ($artisan_id !== null) {
            $get_products = mysqli_prepare($conn, "SELECT * FROM products WHERE artisan_id = ?");
            mysqli_stmt_bind_param($get_products, "i", $artisan_id);
            mysqli_stmt_execute($get_products);
            $result = mysqli_stmt_get_result($get_products);

            while($row=mysqli_fetch_assoc($result))
                {
                $product_id=$row['product_id'];
                $product_name=$row['product_name'];
                $product_image=$row['product_image'];
                $product_stock=$row['stock_quantity'];
                $product_price=$row['product_price'];
                $approval_status = $row['approval_status'];
                $status=$row['status'];
                $number++;

                $status_class = 'status-' . strtolower(htmlspecialchars($status));
                $approval_class = 'status-' . strtolower(htmlspecialchars($approval_status));
                ?>
                        <tr>
                        <td><?php echo (int) $number; ?></td>
                        <td><?php echo htmlspecialchars($product_name); ?></td>
                        <td><img src='./images/<?php echo htmlspecialchars($product_image);?>' class='product-thumb'/></td>
                        <td>Ksh <?php echo number_format((float) $product_price, 2);?></td>
                        <td><?php echo (int) $product_stock; ?></td>
                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                        <td><span class="status-badge <?php echo $approval_class; ?>"><?php echo htmlspecialchars($approval_status); ?></span></td>
                        <td><a href='index.php?edit_products=<?php echo (int) $product_id; ?>' title="Edit"><i class='fa-solid fa-pen-to-square'></i></a></td>
                        <td><a href='index.php?delete_products=<?php echo (int) $product_id; ?>' title="Delete" onclick="return confirm('Delete this product?');"><i class='fa-solid fa-trash'></i></a></td>
                     </tr>
         <?php
            }
        } else {
            echo "<tr><td colspan='9' style='text-align:center; padding:1rem;'>No products found for this artisan account.</td></tr>";
        }
        ?>

    </tbody>

</table>
</div>