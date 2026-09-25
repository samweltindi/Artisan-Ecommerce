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
.cart-table{
    width: 70%;
    border-collapse: collapse;
    text-align: center;
}
.cart-table th{
    border: 1px solid #dee2e6;
    background-color: #71ace7;
    padding: 10px;
    text-align: left;
    border: 2px solid #ddd;
}
.cart-table td{
    padding: 10px;
    border: 1px solid #ddd;
    vertical-align: middle;
}
.cart-table img{
    width: 50px;
    height: 50px;
    object-fit: cover;
}
.cart-footer{
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    gap: 20px;
}
.update{
    background-color: #0dcaf0;
    padding-left:16px;
    padding-right:16px;
    padding-top:8px;
    padding-bottom:8px;
    border: 0;
    cursor: pointer;
}
.remove{
   background-color: #0dcaf0;
    padding-left:16px;
    padding-right:16px;
    padding-top:8px;
    padding-bottom:8px;
    border: 0;
    cursor: pointer;  
}
.qty{
    display: block;
    width: 50%;
    padding: 6px 12px;
    font-size: 16px;
    line-height: 1.5;
    color: #212529;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 4px;
}
.subtotal{
    display: flex;
    margin: 20px;
    gap: 20px;
}
.shoping{
    background-color: #0dcaf0;
    padding-left:16px;
    padding-right:16px;
    padding-top:8px;
    padding-bottom:8px;
    border: 0;
    cursor: pointer;
}
    </style>
</head>
<body>
    <header>
        <div class="logo">
         <img src="logo1.svg" alt="logo"/>
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

<section class="cart">
 <table class="cart-table">
    <thead>
        <tr>
            <th>Product name</th>
            <th>Product image</th>
            <th>Quantity</th>
            <th>Price</th>
        
            <th colspan="2">Operations</th>
        </tr>
  </thead>
  <tbody>
    
    <?php
        $get_ip_add = getIPAddress();
        $total_price = 0;
        $cart_query = "select * from cart_items where ip_address='$get_ip_add'";
        $result = mysqli_query($conn,$cart_query);
        while($row = mysqli_fetch_array($result)){
            $product_id = $row['product_id'];
            $select_products = "select * from products where product_id='$product_id'";
            $result_products = mysqli_query($conn,$select_products);
            while($row_product_price = mysqli_fetch_array($result_products)){
                $price_table = $row_product_price['product_price'];
                $product_name = $row_product_price['product_name'];
                $product_image = $row_product_price['product_image'];
                $product_price = array($row_product_price['product_price']);
                $product_values = array_sum($product_price);
                $total_price += $product_values;
      
        
    ?>
                <tr>
                   <td><?php echo $product_name; ?></td>
                   <td><img src="./Artisans/images/<?php echo $product_image; ?>" alt="Product image" class="cart_image" width="100" height="100"></td>
                   <td><input type="number" name="qty" id="" class="qty" min="1" value="1"></td>

                   <td><?php echo $price_table; ?>/-</td>
                   
                   <td>
                    <!--<button class="bg-info px-3 py-2 border-0">Remove</button>-->
                    <input type="submit" value="Update Cart" class="update" name="update_cart">
                    <input type="submit" value="Remove Cart" class="remove" name="remove_cart">
                    
                    <?php
                    //function to update quantity and total price
                    $get_ip_add = getIPAddress();
                    if(isset($_POST['update_cart'])){
                        $quantities = $_POST['qty'];
                        $update_cart = "update cart_items set quantity=$quantities where ip_address='$get_ip_add'";
                        $result_products_quantity = mysqli_query($conn,$update_cart);
                        $total_price = $total_price* $quantities;
                    }
                    ?>
                    


                   </td>
                </tr>
        <?php       
        }
        }
        ?>
  </tbody>

  <footer class="cart-footer">
    <tr>
        
    </tr>
  </footer>


 </table>
 <!-- subtotal -->
          <div class="subtotal">
            <h4 class="px-3">Subtotal: <strong style="color: #0dcaf0;">ksh <?php echo $total_price; ?></strong></h4>
            <button class="shoping"><a href="./index.php" style="text-decoration: none;color: black;">Continue Shopping</a></button>
            <button class="shoping"><a href="./customers/checkout.php" style="text-decoration: none;color: black;">Checkout</a></button>
           <!-- <input type="submit" value="Checkout" class="bg-info px-3 py-2 border-0 mx-3" name="checkout">-->
          </div>
</section>

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