<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../database_connection.php');
session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtisanOrders Ecommerce</title>
    <!-- a link to css file-->
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>

    </style>
</head>
<body>
    <header>
        <div class="logo">
         <img src="../logo1.svg" alt="logo"/>
        </div>
        <form class="Search_form" action="search_product.php" method="get">
        <input class="form-btn" type="search" name="search_data" placeholder="Search products..." aria-label="Search"/>
        <input class="submit-btn" type="submit" name="search_data_products" value="Search">
        </form>

    </header>
      <nav>
  <div>
    <ul>
      
    <?php
    
    if(!isset($_SESSION['username']))
      {
        echo "<li>
      <a class='nav-link' href='#'>Welcome Customer</a></li>";
      }
      else{
        echo "<li class='nav-item'><a class='nav-link' href='#'>Welcome ".$_SESSION['username']."</a></li>";

      }
    if(!isset($_SESSION['username']))
      {
        echo "<li><a class='nav-link' href='./customers/login.php'>login</a></li>";
      }
      else{
        echo "<li class='nav-item'><a class='nav-link' href='./customers/logout.php'>logout</a></li>";

      }
    ?>
      </ul>
  </div>
</nav>
<div class="row">
  <?php
  // Guests get sent to log in first. Logged-in users get sent into the
  // real checkout flow (delivery address -> M-Pesa payment) - this page
  // never had any actual checkout content of its own.
  if(!isset($_SESSION['username']))
    {
      echo "<script>window.open('./login.php','_self')</script>";
    }
    else{
      echo "<script>window.open('./delivery_address.php','_self')</script>";
    }
  ?>
</div>
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
  <p class="footerP">Copyright &copy2026 ArtisanOrders, All rights reserved.</p>
</footer>
</body>
</html>