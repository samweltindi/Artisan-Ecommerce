<?php
include('../database_connection.php');
if(empty($_SESSION['role']) || $_SESSION['role'] !== 'admin')
            {
                echo "<script>alert('Access denied. ')</script>";
                echo "<script>window.location.href='../Artisans/login.php';</script>";
                exit;
            }
            //csrf token generation
            csrf_token();
if(isset($_POST['insert_cat'])){
    //verify csrf token
    csrf_verify();
    $category_name = trim($_POST['cat_name'] ?? '');
    if ($category_name === '') {
        echo "<script>alert('Category name is required')</script>";
    } else {
        //compares data to be entered with the data already in the database to avoid duplicates
        $select_stmt = mysqli_prepare($conn, "select category_id from categories where category_name = ?");
        mysqli_stmt_bind_param($select_stmt, "s", $category_name);
        mysqli_stmt_execute($select_stmt);
        mysqli_stmt_store_result($select_stmt);
        $exists = mysqli_stmt_num_rows($select_stmt) > 0;
        mysqli_stmt_close($select_stmt);

        if($exists){
            echo "<script>alert('Category already exists');</script>";
        } else {
            //else if the category does not exist, insert it into the database
            $insert_stmt = mysqli_prepare($conn, "insert into categories(category_name) values(?)");
            mysqli_stmt_bind_param($insert_stmt, "s", $category_name);
            $result = mysqli_stmt_execute($insert_stmt);
            mysqli_stmt_close($insert_stmt);
            if($result){
                echo "<script>alert('Category has added successfully'); window.location.href='./index.php?view_categories';</script>";
            } else {
                echo "<script>alert('Could not add category. Please try again.');</script>";
            }
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
.form1 input[type="text"]{
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #dfe3e8;
    border-radius: 8px;
    background-color: #fbfbfc;
    color:#2f3b4c;
    font-size: 15px;
}
.form1 input[type="text"]:focus{
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
    <h1>Insert Categories</h1>
    <form action="" method="post" enctype="multipart/form-data"  class="form1">
        <?php csrf_field(); ?>
        <div>
            <input type="text" name="cat_name" id="cat_name" required="required" placeholder="Insert Categories" aria-label="Categories" aria-describedby="basic-addon1">
        </div>
        <input type="submit" value="Insert Category" name="insert_cat">
    </form>
</div>