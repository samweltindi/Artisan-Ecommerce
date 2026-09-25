<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('database_connection.php');
include('functions/sharedfunctions.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtisanOrders Ecommerce</title>
    <!-- a link to css file-->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
   
    </style>
</head>
<body>
    <header>
        <div class="logo">
         <img src="logo1.svg" alt="logo"/>
        </div>
        <form class="Search_form" action="./search_product.php" method="get">
        <input class="form-btn" type="search" name="search_data" placeholder="Search products..." aria-label="Search"/>
        <input class="submit-btn" type="submit" name="search_data_products" value="Search">
        </form>
        <aside>
        <span class="useraccount"><i class="fa-solid fa-user"></i></span>
        <p style="padding:5px;">
        <a href="customers/login.php">Login</a> | <a href="customers/registration.php">Register</a><br>
        <strong><a href="Artisans/registration.php">Become a seller</a></strong> | <strong><a href="Artisans/login.php">Login to seller</a></strong>
        </p>
        <p style="display:flex; align-items:center; gap:8px;font-weight:600;">
            <a href="#"><i class="fa fa-shopping-cart" style="font-size:28px"></i><sup></sup></a> 
        </p>
       </aside>
    </header>

      
    <nav>
  <div>
    <ul>
    <li class="category">
          <h4>Categories</h4>
          <!--categories to be displayed dynamically from the database-->
        </li>
        <?php
        //calling function to get categories
        getcategories();

        ?>
      </ul>
  </div>
</nav>
<main>
  <section class="product-grid">
    <!-- products grids to be here-->
     <?php
    //calling function to display all products
    //getallproducts();
    //displays products according to category clicked
    products_category_display();
    //Does search product function
    search_product();
    ?>
</section>
<article>
  <h2>ArtisanOrders - Online Shopping and Ecommerce marketplace in Kenya</h2>
  <h3>Welcome to ArtisanOrders</h3>
  <p>ArtisanOrders is an online shopping site and an ecommerce store which offer local handmade artisan products to buyers in Kenya. Discover a diverse range of products from our trusted sellers and sellers, ensuring quality and authenticity in every purchase. With our user-friendly platform, you can explore, compare and buy with ease.</p>
  <p>The platform: ArtisanOrders platform offers visibility to handmade and authencity in every purchase. With our user-friendly platform, you can explore, compare and buy with ease.</p>
  <p>ArtisanOrders is not just a market place. It is a collaborative ecosystem. We've forged strong partnership with local artisans and small scale manufacturers to bring you best prices and authentic products.</p>
</article>
</main>
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