<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../database_connection.php');
include('../functions/sharedfunctions.php');
//token generation
csrf_token();
if(isset($_POST['insert_product'])){
    //csrf check
    csrf_verify();
   $product_name = trim($_POST['product_name'] ?? '');
   $product_description = trim($_POST['description'] ?? '');
   $product_keywords = trim($_POST['keywords'] ?? '');
   $product_categories = $_POST['product_categories'] ?? '';
   $price = $_POST['product_price'] ?? '';
   $product_status = 'true';
   $stock_quantity = $_POST['quantity'] ?? '';

   // ---- required-field checks ----
   if($product_name === '' or $product_description === '' or $product_keywords === '' or 
   $product_categories === '' or $price === '' or $stock_quantity === '' or empty($_FILES['image1']['name']))
    {
        echo "<script>alert('Please fill all fields!')</script>";
        exit();
    }

   // ---- length limits (matches varchar(255) columns) ----
   if(strlen($product_name) > 255 or strlen($product_description) > 255 or strlen($product_keywords) > 255)
    {
        echo "<script>alert('One of your text fields is too long (max 255 characters).')</script>";
        exit();
    }

   // ---- type / range checks ----
   if(!ctype_digit((string)$product_categories))
    {
        echo "<script>alert('Invalid category selected.')</script>";
        exit();
    }
   if(!is_numeric($price) || $price <= 0)
    {
        echo "<script>alert('Price must be a positive number.')</script>";
        exit();
    }
   if(!ctype_digit((string)$stock_quantity))
    {
        echo "<script>alert('Quantity must be a whole number of 0 or more.')</script>";
        exit();
    }

   // ---- image upload validation ----
   if($_FILES['image1']['error'] !== UPLOAD_ERR_OK)
    {
        echo "<script>alert('Image upload failed. Please try again.')</script>";
        exit();
    }
   $temp_image = $_FILES['image1']['tmp_name'];
   $product_image = $_FILES['image1']['name'];

   // check actual file content, not just the extension (prevents disguised .php uploads)
   $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp','image/gif'];
   $detected_mime = mime_content_type($temp_image);
   $max_bytes = 2 * 1024 * 1024; // 2MB

   if(!in_array($detected_mime, $allowed_mimes))
    {
        echo "<script>alert('Image must be a JPG,GIF, PNG, or WEBP file.')</script>";
        exit();
    }
   if($_FILES['image1']['size'] > $max_bytes)
    {
        echo "<script>alert('Image must be smaller than 2MB.')</script>";
        exit();
    }

    //obtaining artisan id
    $username = $_SESSION['username'];
    $get_artisanID = mysqli_prepare($conn, "SELECT artisan_id FROM artisans WHERE username = ?");
    mysqli_stmt_bind_param($get_artisanID, "s", $username);
    mysqli_stmt_execute($get_artisanID);
    $artisan_result = mysqli_stmt_get_result($get_artisanID);
    $artisan_row = mysqli_fetch_assoc($artisan_result);
    mysqli_stmt_close($get_artisanID);

    if(!$artisan_row)
        {
            echo "<script>alert('Could not identify your artisan account. Please login again.')</script>";
            exit();
        }
    $artisan_id = $artisan_row['artisan_id'];

    // ---- duplicate product check (same artisan, same product name, case-insensitive) ----
    $dup_check_stmt = mysqli_prepare($conn, "SELECT product_id FROM products WHERE artisan_id = ? 
    AND LOWER(product_name) = LOWER(?)");
    mysqli_stmt_bind_param($dup_check_stmt, "is", $artisan_id, $product_name);
    mysqli_stmt_execute($dup_check_stmt);
    mysqli_stmt_store_result($dup_check_stmt);
    $dup_count = mysqli_stmt_num_rows($dup_check_stmt);
    mysqli_stmt_close($dup_check_stmt);

    if($dup_count > 0)
        {
            echo "<script>alert('You already have a product with this name. Edit the existing listing 
            instead of adding a duplicate.')</script>";
            exit();
        }

    //moving product image to folder
    if(!move_uploaded_file($temp_image,"./images/$product_image"))
        {
            echo "<script>alert('Could not save the uploaded image. Please try again.')</script>";
            exit();
        }

    //inserting products into the database
    //admin must approve product before listed in product catalogue
    $approval_status = 'pending';
    $insert_product_stmt= "insert into products(category_id,artisan_id,product_name,
    product_description,product_keywords,product_image,
    stock_quantity,product_price,date,status,approval_status) values(?,?,?,?,?,
    ?,?,?,NOW(),?,?)";
    $stmt = mysqli_prepare($conn,$insert_product_stmt);
    mysqli_stmt_bind_param($stmt,"iissssidss",$product_categories,$artisan_id,$product_name,
    $product_description,$product_keywords,$product_image,$stock_quantity,$price,$product_status,
    $approval_status);

    //query execution
    $product_query = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if($product_query)
        {
            echo "<script>alert('Product added successfully')</script>";
            echo "<script>window.open('./index.php','_self')</script>";
        }
    else
        {
            // roll back the saved file if the DB insert failed
            @unlink("./images/$product_image");
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
                <?php csrf_field();?>
                <div class="form-group">
                    <!--Product name field-->
                    <label for="first_name" class="">Product Name</label>
                    <input type="text" id="product_name" class="" placeholder="Enter product Name..." 
                    autocomplete="off" required maxlength="255" name="product_name"/>

                </div>
                <div class="form-group">
                    <!--Product description field-->
                    <label for="last_name" class="">Product Description</label>
                    <input type="text" id="description" class="" placeholder="Enter product description..." 
                    autocomplete="off" required maxlength="255" name="description"/>

                </div>
                <div class="form-group">
                    <!--Product keyword field-->
                    <label for="email" class="">Product Keywords</label>
                    <input type="text" id="keywords" class="" name="keywords" placeholder="Enter product keywords..." 
                    autocomplete="off" required maxlength="255"/>

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
                    echo "<option value='$category_id'>$category_name</option>";
                }
                ?> 
                </select>
             </div>  
                <div class="form-group">
                    <!--Product image field-->
                    <label for="image" class="">Product image</label>
                    <input type="file" class="form-select" name="image1" 
                    accept="image/png, image/jpeg, image/webp" required/>

                </div>
                <div class="form-group">
                    <!--Product quantity field-->
                    <label for="email" class="">Product Quantity</label>
                    <input type="number" id="quantity" class="" name="quantity" autocomplete="off" 
                    placeholder="Enter quantity..." required min="1" step="1"/>

                </div>
                <div class="form-group">
                    <!--Product Price field-->
                    <label for="email" class="">Product Price</label>
                    <input type="number" id="price" class="" name="product_price" placeholder="Enter product price..." 
                    autocomplete="off" required min="0.01" step="0.01"/>

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