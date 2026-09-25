<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<style>
.products-card{
    max-width: 700px;
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
</style>

<div class="products-card">
<h3>All Categories</h3>
<table class="table1">
    <thead>
        <tr>
            <th>Serial number</th>
            <th>Category Name</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $select_cart ="select * from categories";
        $result = mysqli_query($conn,$select_cart);
        $number=0;
        while($row=mysqli_fetch_assoc($result)){
            $category_name = $row['category_name'];
            $number++;
        ?>
     <tr>
        <td><?php echo (int) $number;?></td>
        <td><?php echo htmlspecialchars($category_name); ?></td>
     </tr>
     <?php
        }
        if ($number === 0) {
            echo "<tr><td colspan='2' style='text-align:center; padding:1rem;'>No categories found.</td></tr>";
        }
    ?>

    </tbody>

</table>
</div>