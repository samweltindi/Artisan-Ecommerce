<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../database_connection.php');
include('../functions/sharedfunctions.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart  </title>
   
    <!--font awesome link-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!--Css file-->
    <link rel="stylesheet" href="../style.css">  
    <style>
        body{
            background: #E8F0F3;
        }

.manage{
    display: flex;
    background: white;
    align-items: center;
    justify-content: center;
    padding: 15px;
}
.row123{
    display: flex;
    padding: 15px;
    align-items: center;
    list-style-type: none;
    gap: 20px;
    margin-top: 10px;
    margin-bottom: 10px;
    background-color: #f8f9fa;
}
.row123 li{
    display : inline;
}
.row123 a{
    text-decoration: none;
    color: black;
    padding: 10px 15px;
    border-radius: 4px;
}
.row123 a:hover{
    background-color: #35c7c9;
    color:#1c2b3a;
    font-weight: 600;
}
.btn-artisan{
  
  display: inline-block;
  padding: 12px 24px;
  font-size: 16px;
  font-weight: 600;
  text-align: center;
  text-decoration: none;
  border: none;
  cursor : pointer;
  color: white;
}
.my-table{
    width: 100%;
    margin-bottom: 1rem;
    color: #212529;
    vertical-align: top;
    border-collapse: collapse;
    border: 1px solid #dee2e6;
    
}
.my-table th, .my-table td{
    padding :0.5rem 0.5rem;
    border: 1px solid #dee2e6;
    vertical-align: middle;
}
.my-table thead th{
    background-color : #f8f9fa;
    font-weight:600;
}
.layout{
    display: flex;
    flex-direction: row;
    gap : 20px;
    min-height: auto;
    
}
.sidebar{
    width: 300px;
    background-color: white;
    padding: 20px;
    margin-bottom: 20px;
    height:auto;
    margin-left: 20px;
    margin-top: 20px;
    border-radius: 8px;
    overflow: hidden;

    
}
.sidebar a{
    display: block;
    color: black;
    text-decoration: none;
    padding: 15px 20px;
    margin: 0 - 20px;
    transition: background-color 0.3s ease;
    font-size: 16px;

}
.sidebar a:hover{
    background-color:#E8F0F3;
    weight: 600;
    width: 100%;
    
}

.sidebar ul{
    list-style-type: none;
    padding: 0;
    margin: 0;
}
footer{
    width: 100%;
    margin:0;
}
.sidebar1{
    margin-left: 10px;
    width: 600px;
}
.profile_title{
    background-color: #35c7c9;
    

}
.nav-item{
    list-style: none;
}
.sidebar h3{
    background-color:#C9DDE4; 
    color:#333; 
    padding:15px;
    margin: -20px -20px 20px -20px;
}


    </style>
</head>
 <body>
    <!--navbar-->
    <div class="container">
        <!--first child-->

        <header>
       <div class="logo">
         <img src="../logo1.svg" alt="logo"/>
        </div>
        <form class="Search_form" action="search_product.php" method="get">
        <input class="form-btn" type="search" name="search_data" placeholder="Search products..." aria-label="Search"/>
        <input class="submit-btn" type="submit" name="search_data_products" value="Search">
        </form>
        <aside>
        <span class="useraccount"><i class="fa-solid fa-user"></i></span>
        <p style="padding:5px;">
        <?php
            if(!isset($_SESSION['username']))
            {
                echo "<li class='nav-item'>Welcome Guest</li>";
            }
            else{
                echo "<li class='nav-item'>Welcome ".htmlspecialchars($_SESSION['username'])."</li>";

            }
            if(!isset($_SESSION['username']))
            {
                echo "<li class='nav-item'><a class='nav-link' href='./customers/login.php'>login</a></li>";
            }
           
    ?>
        </p>
        <p style="display:flex; align-items:center; gap:8px;font-weight:600;">
            <a href="cart.php"><i class="fa fa-shopping-cart" style="font-size:28px"></i><sup><?php cart_item(); ?></sup></a> 
        </p>
       </aside>

    </header>

               <!--Third child-->
       
<div class="layout">
    <div class="sidebar">
        
            <h3>Your Profile</h3>
            <ul>
            <li><a href="profile.php?edit_account">Edit account</a></li>
            <li><a href="profile.php?my_orders">My Orders</a></li>
            <li><a href="profile.php?my_account">Account Details</a></li>
            <li><a href="profile.php?delivery">My deliveries</a></li>
            <li><a href="profile.php?delete_account">Delete account</a></li>
            <li><a href="./logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="sidebar1">
        <?php
        //default to my account after successful login
        $any_tab_selected = isset($_GET['edit_account'])
            || isset($_GET['my_orders']) || isset($_GET['delete_account'])
            || isset($_GET['my_account']) || isset($_GET['delivery']);

        if(isset($_GET['edit_account'])){
            include('edit_account.php');
        }
        if(isset($_GET['my_orders'])){
            include('my_orders.php');
        }
        if(isset($_GET['delete_account'])){
            include('delete_account.php');
        }
        if(isset($_GET['my_account']))
            {
                include('my_account.php');
            }
        if(isset($_GET['delivery']))
            {
                include('delivery.php');
            }
        if(!$any_tab_selected){
            include('my_account.php');
        }
        ?>
    </div>
</div>                     
    
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
  