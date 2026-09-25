<?php
$category_title = '';
$edit_category = 0;
//guardrails the file
if(empty($_SESSION['role']) || $_SESSION['role'] !== 'admin')
    {
        header("Location: ../Artisans/login.php");
        exit;
    }
if(empty($_SESSION['csrf_token']))
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }    

if(isset($_GET['edit_category']))
    {
        if (!ctype_digit((string) $_GET['edit_category'])) {
            echo "<p>Invalid category.</p>";
            exit;
        }
        $edit_category = (int) $_GET['edit_category'];
        $get_stmt = mysqli_prepare($conn, "select * from categories where category_id = ?");
        mysqli_stmt_bind_param($get_stmt, "i", $edit_category);
        mysqli_stmt_execute($get_stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($get_stmt));
        mysqli_stmt_close($get_stmt);
        if (!$row) {
            echo "<p>Category not found.</p>";
            exit;
        }
        $category_title = $row['category_name'];
    }
    if(isset($_POST['edit_cat'])){
        csrf_verify();
        $cat_name = trim($_POST['category_name'] ?? '');
        if ($cat_name === '') {
            echo "<script>alert('Category name is required')</script>";
        } else {
            $update_stmt = mysqli_prepare($conn, "update categories set category_name = ? where category_id = ?");
            mysqli_stmt_bind_param($update_stmt, "si", $cat_name, $edit_category);
            $result_cat = mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
            if($result_cat)
                {
                    echo "<script>alert('Category updated successfully')</script>";
                    echo "<script>window.open('./index.php?view_categories','_self')</script>";
                } else {
                    echo "<script>alert('Update failed. Please try again.')</script>";
                }
        }
    }

?>
<style>
.products-card{
    max-width: 500px;
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
.form1 label{
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: #4a5568;
    display: inline-block;
}
.form1 input{
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #dfe3e8;
    border-radius: 8px;
    background-color: #fbfbfc;
    color:#2f3b4c;
    font-size: 15px;
}
.form1 input:focus{
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 0 3px rgba(53,199,201, 0.2);
    background-color: #ffffff;
}
.form1 input[type="submit"]{
    background-color: #0cc012;
    color: white;
    border: none;
    cursor: pointer;
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
</style>
<div class="products-card">
    <h1>Edit Category</h1>
    <form action="" method="post" enctype="multipart/form-data"  class="form1">
        <?php csrf_field(); ?>
        <div>
            <label for="category_name">Category Name</label>
            <input type="text" value="<?php echo htmlspecialchars($category_title);?>" name="category_name" id="category_name" required="required">
        </div>
        <input type="submit" value="Update Category" name="edit_cat">
    </form>
</div>