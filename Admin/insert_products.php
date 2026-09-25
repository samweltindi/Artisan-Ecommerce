<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../database_connection.php');
include('../functions/sharedfunctions.php');
//guardrails the insert product file
if(empty($_SESSION['role']) || $_SESSION['role'] !== 'admin')
    {
        header("Location: ../Artisans/login.php");
        exit;
    }
//generate csrf token
csrf_token();  

if(isset($_POST['insert_product'])){
    //verify token
    csrf_verify();
   $product_name = trim($_POST['product_name'] ?? '');
   $product_description = trim($_POST['description'] ?? '');
   $product_keywords = trim($_POST['keywords'] ?? '');
   $product_categories = $_POST['product_categories'] ?? '';
   $price = $_POST['product_price'] ?? '';
   $product_status = 'true';
   $stock_quantity = $_POST['quantity'] ?? '';
   // The admin picks which artisan this product belongs to explicitly -
   // previously this always grabbed whichever artisan happened to come
   // back first from "select * from artisans", silently mis-assigning
   // every admin-added product to one arbitrary seller.
   $artisan_id = $_POST['artisan_id'] ?? '';

   //checking empty condition
   if($product_name === '' or $product_description === '' or $product_keywords === '' or
   $product_categories === '' or $price === '' or $stock_quantity === '' or $artisan_id === '' or empty($_FILES['image1']['name']))
    {
        echo "<script>alert('Please fill all fields!')</script>";
        exit();
    }

   if(strlen($product_name) > 255 or strlen($product_description) > 255 or strlen($product_keywords) > 255)
    {
        echo "<script>alert('One of your text fields is too long (max 255 characters).')</script>";
        exit();
    }
   if(!ctype_digit((string)$product_categories))
    {
        echo "<script>alert('Invalid category selected.')</script>";
        exit();
    }
   if(!ctype_digit((string)$artisan_id))
    {
        echo "<script>alert('Invalid artisan selected.')</script>";
        exit();
    }
   if(!is_numeric($price) || $price <= 0)
    {
        echo "<script>alert('Price must be a positive number.')</script>";
        exit();
    }
   if(!ctype_digit((string)$stock_quantity))
    {
        echo "<script>alert('Quantity must be a whole number of 1 or more.')</script>";
        exit();
    }

   // confirm the chosen artisan actually exists
   $artisan_check_stmt = mysqli_prepare($conn, "select artisan_id from artisans where artisan_id = ?");
   mysqli_stmt_bind_param($artisan_check_stmt, "i", $artisan_id);
   mysqli_stmt_execute($artisan_check_stmt);
   $artisan_exists = mysqli_fetch_assoc(mysqli_stmt_get_result($artisan_check_stmt));
   mysqli_stmt_close($artisan_check_stmt);
   if (!$artisan_exists) {
        echo "<script>alert('Selected artisan not found.')</script>";
        exit();
   }

   // ---- image upload validation (same approach as the artisan-side form) ----
   if($_FILES['image1']['error'] !== UPLOAD_ERR_OK)
    {
        echo "<script>alert('Image upload failed. Please try again.')</script>";
        exit();
    }
   $temp_image = $_FILES['image1']['tmp_name'];
   $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
   $detected_mime = mime_content_type($temp_image);
   $max_bytes = 2 * 1024 * 1024; // 2MB

   if(!in_array($detected_mime, $allowed_mimes))
    {
        echo "<script>alert('Image must be a JPG, GIF, PNG, or WEBP file.')</script>";
        exit();
    }
   if($_FILES['image1']['size'] > $max_bytes)
    {
        echo "<script>alert('Image must be smaller than 2MB.')</script>";
        exit();
    }
   // Sanitize the filename before it's used in a filesystem path.
   $product_image = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['image1']['name']));

   if(!move_uploaded_file($temp_image,"../Artisans/images/$product_image"))
    {
        echo "<script>alert('Could not save the uploaded image. Please try again.')</script>";
        exit();
    }

   //inserting products into the database
   $insert_stmt = mysqli_prepare($conn, "insert into products(category_id,artisan_id,product_name,
        product_description,product_keywords,product_image,stock_quantity,product_price,date,status,approval_status)
        values(?,?,?,?,?,?,?,?,NOW(),?,?)");
   $approval_status = 'approved'; // admin-added products are pre-approved
   mysqli_stmt_bind_param(
        $insert_stmt, "iissssidss",
        $product_categories, $artisan_id, $product_name, $product_description,
        $product_keywords, $product_image, $stock_quantity, $price, $product_status, $approval_status
   );
   $product_query = mysqli_stmt_execute($insert_stmt);
   mysqli_stmt_close($insert_stmt);

   if($product_query)
        {
            echo "<script>alert('Product added successfully')</script>";
            echo "<script>window.open('./index.php','_self')</script>";
        }
    else
        {
            @unlink("../Artisans/images/$product_image");
            echo "<script>alert('Could not save the product. Please try again.')</script>";
        }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!--Css file-->
    <link rel="stylesheet" href="../styles.css">
    <style>
    *,*::before, *::after{
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
body{
    font-family: system-ui, -apple-system, BlinkMacSystemFont,"Segoe UI",Roboto, Arial, Helvetica, sans-serif;
    background: #E8F0F3;
    color: #222;
    display:flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-x:hidden;

}
.productcontainer{
    background-color:white;
    padding: 40px 30px;
    border-radius: 16px;
    width: 100%;
    max-width:600px;


}
h2{
    text-align:center;
    margin-bottom:30px;
    font-size:28px;
    color:green;
}
.form-group{
    margin-bottom: 20px;
}
label{
    display:block;
    margin-bottom: 8px;
    font-weight:500;
    font-size: 14px;
}
.form-select{
    width:100%;
    padding: 14px 16px;
    border: 2px solid #e1e1e1;
    border-radius: 8px;
    font-size:16px;
}
input{
    width:100%;
    padding: 14px 16px;
    border: 2px solid #e1e1e1;
    border-radius: 8px;
    font-size:16px;

}
.product-btn{
    
    padding: 14px;
    color:white;
    background: #0E4D3C;
    cursor: pointer;
    border: none;
    margin:auto;
    margin-bottom: 4px;
    width: 50%;
}
    </style>
</head>
<body>
    <div class="productcontainer">
      <h2 class="textcenter">
        Insert Products
      </h2>
      <div class="registration">
        <div class="">
            <form action="" method="post" enctype="multipart/form-data">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <!--Artisan field-->
                    <label for="artisan_id" class="">Artisan (product owner)</label>
                    <select name="artisan_id" id="artisan_id" class="form-select" required>
                        <option value="">Select an artisan</option>
                        <?php
                        $artisan_list = mysqli_query($conn, "select artisan_id, full_name, business_name from artisans where approved = 1 order by full_name");
                        while($a = mysqli_fetch_assoc($artisan_list)){
                            $label = $a['full_name'] . ' (' . $a['business_name'] . ')';
                            echo "<option value='" . (int) $a['artisan_id'] . "'>" . htmlspecialchars($label) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <!--Product name field-->
                    <label for="first_name" class="">Product Name</label>
                    <input type="text" id="product_name" class="" placeholder="Enter product Name..." autocomplete="off" required="required" maxlength="255" name="product_name"/>

                </div>
                <div class="form-group">
                    <!--Product description field-->
                    <label for="last_name" class="">Product Description</label>
                    <input type="text" id="description" class="" placeholder="Enter product description..." autocomplete="off" required="required" maxlength="255" name="description"/>

                </div>
                <div class="form-group">
                    <!--Product keyword field-->
                    <label for="email" class="">Product Keywords</label>
                    <input type="text" id="keywords" class="" name="keywords" placeholder="Enter product keywords..." autocomplete="off" required maxlength="255"/>

                </div>
                <!--product categories-->
            <div class="form-group">
               <select name="product_categories" id="" class="form-select" required>
                <option value="">Select a category</option>
                <?php
                $select_query = "select * from categories";
                $result_query = mysqli_query($conn,$select_query);
                while($row = mysqli_fetch_assoc($result_query)){
                    $category_name = $row['category_name'];
                    $category_id = $row['category_id'];
                    echo "<option value='" . (int) $category_id . "'>" . htmlspecialchars($category_name) . "</option>";
                }
                ?> 
                </select>
             </div>  
                <div class="form-group">
                    <!--Product image field-->
                    <label for="image" class="">Product image</label>
                    <input type="file" class="form-select" name="image1" accept="image/png, image/jpeg, image/webp, image/gif" required/>

                </div>
                <div class="form-group">
                    <!--Product quantity field-->
                    <label for="email" class="">Product Quantity</label>
                    <input type="number" id="quantity" class="" name="quantity" autocomplete="off" placeholder="Enter quantity..." required min="0" step="1"/>

                </div>
                <div class="form-group">
                    <!--Product Price field-->
                    <label for="email" class="">Product Price</label>
                    <input type="number" id="price" class="" name="product_price" placeholder="Enter product price..." autocomplete="off" required min="0.01" step="0.01"/>

                </div>
              
                
                <div class="submit-login">
                    <input type="submit" value="Insert Product" class="product-btn" name="insert_product"/>
                    
                </div>
            </form>

        </div>
      </div>
    </div>
</body>
</html>