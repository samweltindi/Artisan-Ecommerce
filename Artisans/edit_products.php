<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(empty($_SESSION['username']) || $_SESSION['role'] !== 'artisan')
    {
        echo "<script>alert('Denied access');</script>";
        header("Location: ./login.php");
        exit;
    }
    //csrf token generation
    csrf_token();
if(isset($_GET['edit_products']))
    {
        //csrf checks
        csrf_verify();
        if (!ctype_digit((string) $_GET['edit_products'])) {
            echo "<p>Invalid product.</p>";
            exit;
        }
        $edit_id = (int) $_GET['edit_products'];

        $get_stmt = mysqli_prepare($conn, "select * from products where product_id = ?");
        mysqli_stmt_bind_param($get_stmt, "i", $edit_id);
        mysqli_stmt_execute($get_stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($get_stmt));
        mysqli_stmt_close($get_stmt);

        if (!$row) {
            echo "<p>Product not found.</p>";
            exit;
        }

        $product_name = $row['product_name'];
        $product_description = $row['product_description'];
        $product_keywords = $row['product_keywords'];
        $category_id = $row['category_id'];
        $product_image = $row['product_image'];
        $product_price = $row['product_price'];
        $stock = $row['stock_quantity'];

        //fetching category name
        $select_category_stmt = mysqli_prepare($conn, "select * from categories where category_id = ?");
        mysqli_stmt_bind_param($select_category_stmt, "i", $category_id);
        mysqli_stmt_execute($select_category_stmt);
        $row_category = mysqli_fetch_assoc(mysqli_stmt_get_result($select_category_stmt));
        mysqli_stmt_close($select_category_stmt);
        $category_title = $row_category['category_name'] ?? '';
    }
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
.products-card h1{
    font-size: 1.5rem;
    font-weight: 700;
    color: #1c2b3a;
    text-align: left;
    border-bottom: 1px solid #eef1f4;
    padding-bottom: 16px;
    margin-bottom: 24px;
}
.form1{
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.form1 input, .form1 select{
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #dfe3e8;
    border-radius: 8px;
    background-color: #fbfbfc;
    color:#2f3b4c;
    font-size: 15px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}
.form1 input:focus, .form1 select:focus{
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 0 3px rgba(53,199,201, 0.2);
    background-color: #ffffff;
}
.form1 input[type="file"]{
    padding: 8px;
    background-color: #fbfbfc;
}
.form1 input[type="submit"]{
    background-color: #0cc012;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
    padding: 12px;
    width: 50%;
    align-self: center;
    border-radius: 8px;
    font-weight: 600;
    margin-top: 10px;
}
.form1 input[type="submit"]:hover{
    background-color: #0aa60f;
}
.form-label{
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: #4a5568;
    line-height: 1.5;
    display: inline-block;
}
.form1 .d-flex{
    display: flex;
    align-items: center;
    gap: 12px;
}
.form1 .product_img{
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #eef1f4;
}
.form1 small{
    color: #6b7280;
    font-size: 12px;
}
</style>
<div class="products-card">
<h1>Edit Product</h1>
<form action="" method="post" enctype="multipart/form-data" class="form1">
    <?php csrf_field();?>
        <div>
        <label for="product_name" class="form-label">
        Product Name
        </label>
        <input type="text" id="product_name" value="<?php echo htmlspecialchars($product_name); ?>" name="product_name" class="form-control" required="required">
        </div>
        
   <div>
        <label for="product_name" class="form-label">
        Product Description
        </label>
        <input type="text" id="product_description" value="<?php echo htmlspecialchars($product_description); ?>" name="product_description" class="form-control" required="required">
        </div>
    <div>
        <label for="product_keywords" class="form-label">
        Product Keywords
        </label>
        <input type="text" id="product_keywords" value="<?php echo htmlspecialchars($product_keywords); ?>" name="product_keywords" class="form-control" required="required">
    </div>
    <div>
        <label for="product_category" class="form-label">
        Product Categories
        </label>
        <select name="product_category" class="form-select">
                <?php
        $select_category_all ="select * from categories";
        $result_category_all = mysqli_query($conn,$select_category_all);
        while($row_category_all= mysqli_fetch_assoc($result_category_all)){
          $cat_name = $row_category_all['category_name'];
          $cat_id = $row_category_all['category_id'];
          $selected = ((string) $cat_id === (string) $category_id) ? 'selected' : '';
          echo "<option value='" . htmlspecialchars($cat_id) . "' $selected>" . htmlspecialchars($cat_name) . "</option>";
        }
            ?>
        </select>

    </div>
    
   <div>
        <label for="product_image" class="form-label">
        Product image
        </label>
        <div class="d-flex">
        <input type="file" id="product_image" name="product_image" class="form-control w-90 m-auto">
        <img src="../Artisans/images/<?php echo htmlspecialchars($product_image);?>" alt="" class="product_img">
        </div>
        <small>Leave this blank to keep the current image.</small>
    </div>
    <div>
        <label for="product_keywords" class="form-label">
        Stock quantity
        </label>
        <input type="text" id="product_keywords" value="<?php echo htmlspecialchars($stock); ?>" name="product_stock" class="form-control" required="required">
    </div>
    <div>
        <label for="product_price" class="form-label">
        Product Price
        </label>
        <input type="text" id="product_price" value="<?php echo htmlspecialchars($product_price); ?>" name="product_price" class="form-control" required="required">
    </div>
    <div>
        <input type="submit" name="edit_product" value="Update Product">
    </div>

</form>
</div>
<!--Editting products-->
<?php
if(isset($_POST['edit_product']))
    {
        $product_name = trim($_POST['product_name'] ?? '');
        $product_desc = trim($_POST['product_description'] ?? '');
        $product_keywords = trim($_POST['product_keywords'] ?? '');
        $product_category = $_POST['product_category'] ?? '';
        $product_price = $_POST['product_price'] ?? '';
        $stock = $_POST['product_stock'] ?? '';

        if($product_name == '' or $product_desc == '' or $product_keywords=='' or
        $product_category=='' or $stock === '' or $product_price==''){
            echo "<script>alert('Please fill all fields')</script>";
        } elseif (!ctype_digit((string) $product_category)) {
            echo "<script>alert('Invalid category selected.')</script>";
        } elseif (!is_numeric($product_price) || $product_price <= 0) {
            echo "<script>alert('Price must be a positive number.')</script>";
        } elseif (!ctype_digit((string) $stock)) {
            echo "<script>alert('Quantity must be a whole number of 0 or more.')</script>";
        } else {
            $new_image_uploaded = isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK;
            $final_image_name = $product_image; // keep existing by default

            if ($new_image_uploaded) {
                $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $detected_mime = mime_content_type($_FILES['product_image']['tmp_name']);
                $max_bytes = 2 * 1024 * 1024; // 2MB

                if (!in_array($detected_mime, $allowed_mimes)) {
                    echo "<script>alert('Image must be a JPG, GIF, PNG, or WEBP file.')</script>";
                    exit;
                }
                if ($_FILES['product_image']['size'] > $max_bytes) {
                    echo "<script>alert('Image must be smaller than 2MB.')</script>";
                    exit;
                }
                $final_image_name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['product_image']['name']));
                move_uploaded_file($_FILES['product_image']['tmp_name'], "../Artisans/images/$final_image_name");
            }

            $update_stmt = mysqli_prepare($conn, "update products set product_name = ?,
                product_description = ?, product_keywords = ?, category_id = ?,
                product_image = ?, stock_quantity = ?, product_price = ?, date = NOW()
                where product_id = ?");
            mysqli_stmt_bind_param(
                $update_stmt, "sssisddi",
                $product_name, $product_desc, $product_keywords, $product_category,
                $final_image_name, $stock, $product_price, $edit_id
            );
            $result_update = mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);

            if($result_update)
                {
                    echo "<script>alert('Product updated successfully')</script>";
                    echo "<script>window.open('./index.php?view_products','_self')</script>";
                }
            else {
                echo "<script>alert('Could not update the product. Please try again.')</script>";
            }
        }
    }
?>