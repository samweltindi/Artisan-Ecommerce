<?php session_start();
include('../database_connection.php');
include('../functions/sharedfunctions.php');
//guard the admin dashboard
if(empty($_SESSION['username'])){
    header("Location: ../Artisans/login.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard  </title>
   
    <!--font awesome link-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!--Css file-->
    <link rel="stylesheet" href="../style.css">  
    <style>
        body{
            background-color: #E8F0F3;
        }
           .manage{
    display: flex;
    background: white;
    align-items: center;
    justify-content: center;
    padding: 15px;
    margin-top: 10px;
}
.row123{
    display: flex;
    justify-content: space-between;
    padding: 15px;
    align-items: center;
}
.nav-buttons{
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
}
.btn-artisan{
  display: inline-block;
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 600;
  text-align: center;
  text-decoration: none;
  border: 1px solid #cdebd0;
  border-radius: 8px;
  cursor: pointer;
  background-color: #e9f9eb;
  color: #1a8a24;
  transition: background-color 0.2s ease, box-shadow 0.2s ease, transform 0.1s ease;
}
.btn-artisan:hover{
  background-color: #d9f2db;
  box-shadow: 0 2px 8px rgba(26,138,36,0.15);
}
.btn-artisan:active{
  transform: translateY(1px);
}
.btn-artisan.btn-logout{
  background-color: #fdeaea;
  border-color: #f3c2c2;
  color: #a11;
}
.btn-artisan.btn-logout:hover{
  background-color: #fbdada;
  box-shadow: 0 2px 8px rgba(170,17,17,0.12);
}
.my-table{
    width: 100%;
    border-collapse: collapse;
    margin-top: 1.5rem;
}
.my-table th, .my-table td{
    border: 1px solid #dee2e6;
    padding: 0.75rem;
}
.product_img{
            width: 100px;
            object-fit:contain;

        }
        .inputg{
    position: relative;
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    width: 50%;
    margin-bottom: 8px;
        }
        .inputgs{
            display: flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background: green;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }
        .form-control{
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight:400px;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }
        .category-btn{
            background-color: green;
            border: 0;
            padding: 0.5rem;
            margin: 0.5rem;
            
        }
        .div1{
        max-width: 700px;
        margin: 30px auto;
        padding: 20px;
        margin-top: 0;
        }
    .form1{
        display: flex;
        flex-direction: column;
        gap: 20px;
        
    }
    .form1 input{
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #f9f9f9;
        
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .form1 input:focus{
        outline: none;
        border-color: #85b487;
        box-shadow: 0 0 0 3px rgba(53,199,201, 0.2);
    }
    .form1 input[type="submit"]{
        background-color: #09c00f;
        color: white;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
        padding: 10px;
        width: 50%;
        align-self: center;
    

    }
    .form1 input[type="submit"]:hover{
        background-color: #0cc015;
    }
    .cat_name{
        color: green;
        margin-bottom: 15px;
    }
    .table_style{
        width: 80%;
        border-collapse: collapse;
        margin: 30px; 
        padding: 15px;
    }
    .table_style_th{
        border: 1px solid #0c1824;
        padding: 0.75rem;
        background-color: #f2f2f2;
    }
    .table_style_td{
        border: 1px solid #0c1824; 
        padding: 0.75rem; 
        margin-left: 20px;
    }
    .form-label{
        margin-bottom: 0.5rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        display: inline-block;
    }
    .button1{
        display: flex;
        flex-wrap: wrap;
    
    }
   
    </style>
</head>
 <body>
                <!--second child-->
                <div class="manage">
                    <h3 style="align-items: center; padding:2px;">Manage Details(Admin Dashboard)</h3>
               </div>
               <!--Third child-->
                <div class="row123">
                    <div style="margin-bottom: 15px;">
                        <div class="artisan">
                        <p style="margin-bottom: 15px;">Admin name: <?php
                        if(isset($_SESSION['username'])){
                            echo $_SESSION['username'];
                        }
                        ?> </p>
                        </div>
                    <div class="nav-buttons">
                        <a href="insert_products.php" class="btn-artisan">Insert Products</a>
                        <a href="index.php?view_products" class="btn-artisan">View products</a>
                        <a href="index.php?insert_categories" class="btn-artisan">Insert Categories</a>
                        <a href="index.php?view_categories" class="btn-artisan">View Categories</a>
                        <a href="index.php?orders" class="btn-artisan">View orders</a>
                        <a href="index.php?view_customers" class="btn-artisan">Customers</a>
                        <a href="index.php?view_artisans" class="btn-artisan">Artisans</a>
                        <a href="index.php?admin_view_payments" class="btn-artisan">Payments</a>
                        <a href="index.php?sales_report" class="btn-artisan">Sales Report</a>
                        <a href="index.php?manage_deliveries" class="btn-artisan">Deliveries</a>
                        <a href="registration.php" class="btn-artisan">Register New Admin</a>
                        <a href="../customers/logout.php" class="btn-artisan btn-logout">Logout</a>
                    </div>
                    
                </div>
    </div>
    <!--fourth child-->
    <div class="container my-5">
        <?php
        $any_tab_selected = isset($_GET['view_categories']) || isset($_GET['view_products'])
            || isset($_GET['insert_categories']) || isset($_GET['delete_category'])
            || isset($_GET['edit_category']) || isset($_GET['orders'])
            || isset($_GET['delete_orders']) || isset($_GET['view_customers'])
            || isset($_GET['view_artisans']) || isset($_GET['edit_products'])
            || isset($_GET['edit_customers']) || isset($_GET['suspend_customer'])
            || isset($_GET['delete_customer']) || isset($_GET['delete_artisan'])
            || isset($_GET['suspend_artisan']) || isset($_GET['approve_artisan'])
            || isset($_GET['review_artisan']) || isset($_GET['revoke_artisan'])
            || isset($_GET['admin_view_payments']) || isset($_GET['sales_report'])
            || isset($_GET['manage_deliveries']) || isset($_GET['approve_product'])
            || isset($_GET['suspend_product']);

        if(isset($_GET['view_categories'])){
            include('view_categories.php');
        }
        if(isset($_GET['view_products']))
            {
            include('view_products.php');
            }
        if(isset($_GET['insert_categories']))
            {
                include('insert_categories.php');
            }   
        if(isset($_GET['delete_category']))
            {
                include('delete_category.php');
            }     
        if(isset($_GET['edit_category']))
            {
                include('edit_category.php');
            }    
        if(isset($_GET['orders']))
            {
                include('orders.php');
            }
        if(isset($_GET['delete_orders']))
            {
                include('delete_orders.php');
            }  
            if(isset($_GET['view_customers']))
            {
                include('view_customers.php');

            }    
            if(isset($_GET['view_artisans']))
            {
                include('view_artisans.php');
            }  
            if(isset($_GET['edit_products']))
                {
                    include('edit_products.php');
                }
                if(isset($_GET['edit_customers']))
                    {
                        include('edit_customers.php');
                    }
                    if(isset($_GET['suspend_customer']))
                        {
                            include('suspend_customer.php');
                        }
                        if(isset($_GET['delete_customer']))
                            {
                                include('delete_customer.php');
                            }
                            if(isset($_GET['delete_artisan']))
                                {
                                    include('delete_artisan.php');
                                }
                                if(isset($_GET['suspend_artisan']))
                                    {
                                        include('suspend_artisan.php');
                                    }
                                    if(isset($_GET['approve_artisan']))
                                        {
                                            include('approve_artisan.php');
                                        }
                                        if(isset($_GET['review_artisan']))
                                            {
                                                include('review_artisan.php');
                                            }
                                            if(isset($_GET['revoke_artisan']))
                                                {
                                                    include('revoke_artisan.php');
                                                }
                                                if(isset($_GET['admin_view_payments']))
                                                    {
                                                        include('admin_view_payments.php');
                                                    }
                                                    if(isset($_GET['sales_report']))
                                                    {
                                                        include('sales_report.php');
                                                    }
                                                    if(isset($_GET['manage_deliveries']))
                                                        {
                                                            include('manage_deliveries.php');
                                                        }
                                                        if(isset($_GET['approve_product']))
                                                            {
                                                                include('approve_product.php');
                                                            }
                                                            if(isset($_GET['suspend_product']))
                                                                {
                                                                    include('suspend_product.php');
                                                                }
        if(!$any_tab_selected){
            // No tab selected (e.g. straight after login) - default to View Products.
            include('view_products.php');
        }
        ?>
        
    
<!--last child-->
<footer>
  <div class="footerdiv">
    <div>
      <h4>Contact us</h4>
      <p>Email: Samweltindi07@gmail.com<br>Phone: 0742086326</p>
    </div>
    <div>
      <h4>Follow us</h4>
      <span><i class="fa-brands fa-square-facebook"></i> | <i class="fa-brands fa-x-twitter"></i> | <i class="fa-brands fa-whatsapp"></i> | <i class="fa-brands fa-instagram"></i></span>
    </div>
  </div>
  <p class="footerP">Copyright &copy; 2026 ArtisanOrders, All rights reserved.</p>
</footer>
</body>
</html>
  