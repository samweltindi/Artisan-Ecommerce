<?php
// this protects the file, it ensures only admin accesses this file
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Access denied.')</script>";
    echo "<script>window.location.href='../Artisans/login.php';</script>";
    exit;
}
//csrf token
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
.product-thumb{
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #eef1f4;
}
</style>

<div class="products-card">
<h3>All products</h3>
<table class="table1">
    <thead>
        <tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Product Image</th>
            <th>Product Price</th>
            <th>Product Quantity</th>
            <th>Product Description</th>
            <th>Status</th>
            <th>Approval</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $get_products="select*from products";
        $result =mysqli_query($conn,$get_products);
        $number =0;
        while($row=mysqli_fetch_assoc($result))
            {
                $product_id=$row['product_id'];
                $product_name=$row['product_name'];
                $product_image=$row['product_image'];
                $product_stock=$row['stock_quantity'];
                $product_price=$row['product_price'];
                $product_description=$row['product_description'];
                $status=$row['status'];
                $approval_status=$row['approval_status'] ?? 'approved';
                $number++;
                ?>
                        <tr>
                        <td><?php echo (int) $number; ?></td>
                        <td><?php echo htmlspecialchars($product_name); ?></td>
                        <td><img src='../Artisans/images/<?php echo htmlspecialchars($product_image);?>' class='product-thumb'/></td>
                        <td>Ksh <?php echo number_format((float) $product_price, 2);?></td>
                        <td><?php echo (int) $product_stock; ?></td>
                        <td><?php echo htmlspecialchars($product_description); ?></td>
                        <td><?php echo htmlspecialchars($status); ?></td>
                        <td>
                            <?php
                            $badge_colors = ['pending' => '#7a5b00;background:#fff3cd', 'approved' => '#0E4D3C;background:#d4f8dc', 'suspended' => '#a11;background:#fbdada'];
                            $badge_style = $badge_colors[$approval_status] ?? $badge_colors['pending'];
                            ?>
                            <span style="padding:3px 10px;border-radius:14px;font-size:12px;font-weight:600;color:<?php echo $badge_style; ?>">
                                <?php echo htmlspecialchars(ucfirst($approval_status)); ?>
                            </span>
                            <br>
                            <?php if ($approval_status !== 'approved'): ?>
                                <a href='index.php?approve_product&product_id=<?php echo (int)$product_id; ?>' style="color:#0E4D3C;font-size:12px;">Approve</a>
                            <?php endif; ?>
                            <?php if ($approval_status !== 'suspended'): ?>
                                <a href='index.php?suspend_product&product_id=<?php echo (int)$product_id; ?>' style="color:#a11;font-size:12px;margin-left:8px;">Suspend</a>
                            <?php endif; ?>
                        </td>
                        <td><a href='index.php?edit_products=<?php echo (int)$product_id; ?>' title="Edit"><i class='fa-solid fa-pen-to-square'></i></a></td>
                        <td><a href='index.php?delete_products=<?php echo (int)$product_id; ?>' title="Delete" onclick="return confirm('Delete this product?');"><i class='fa-solid fa-trash'></i></a></td>
                     </tr>

         <?php
            }
            if ($number === 0) {
                echo "<tr><td colspan='10' style='text-align:center; padding:1rem;'>No products found.</td></tr>";
            }
        ?>

    </tbody>

</table>
</div>