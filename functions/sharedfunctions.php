<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

//include('connection.php');
//get ip address function
    function getIPAddress() {
        //whether ip is from the share internet
        if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        //whether ip is from the proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        //whether ip is from the remote address
        else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    //displaying categories
function getcategories(){
    global $conn;
    $select_categories = "select * from categories";
    //$stmt = mysqli_prepare($conn,$select_categories);

    $result_categories = mysqli_query($conn,$select_categories);
    while($row_data = mysqli_fetch_assoc($result_categories)){
        $category_name = $row_data['category_name'];
        $category_id = $row_data['category_id'];
        echo "<li class='nav-item'>
        <a href='index.php?category_id=$category_id' class='nav-link text-light'> | $category_name</a>
      </li>";
    }
}
  //displaying all products
  function getallproducts(){
    global $conn;
    if(!isset($_GET['category_id'])){
        $select_products = "select * from products where approval_status = 'approved'";
        $result_query = mysqli_query($conn,$select_products);
        
        
        while($row = mysqli_fetch_assoc($result_query)){
            $product_id = $row['product_id'];
            $product_name = htmlspecialchars($row['product_name']);
            $product_image = htmlspecialchars($row['product_image']);
            $product_price = htmlspecialchars($row['product_price']);
            $category_id = $row['category_id'];
            $stock = htmlspecialchars($row['stock_quantity']);
            echo "
            <div class='product-card'>
            <img class='product-card-image' src='./Artisans/images/$product_image' alt='$product_name'>
            <div class='product-card-info'>
            <h4>$product_name</h4>
            <p>ksh $product_price</p>
            <p>$stock items left</p>
            <a href='index.php?add_to_cart=" . (int)$product_id . "' class='add-to-cart'>Add to cart</a>
            </div>
            </div>";
        }
        // NOTE: stock must only ever be decremented on confirmed payment
        // (see mpesa_callback.php), never here. Viewing the product list
        // must not change inventory — that was the bug causing negative
        // stock counts (it ran on every homepage load, on whatever
        // product happened to be last in the loop above).
    }

  }
  //displays products when the category is clicked
     function products_category_display(){
        global $conn;
        if(isset($_GET['category_id'])){
            $category_id = $_GET['category_id'];
            //$select_category ="select * from products where category_id =$category_id";
            $select_category = "select * from products where category_id=? and approval_status = 'approved'";
            $stmt=mysqli_prepare($conn,$select_category);
            mysqli_stmt_bind_param($stmt,"i",$category_id);
            mysqli_stmt_execute($stmt);
            $result_category = mysqli_stmt_get_result($stmt);
            //$result_category = mysqli_query($conn, $select_category);
            //checks whether category products clicked is available
            $num_of_rows = mysqli_num_rows($result_category);
            if($num_of_rows == 0)
                {
                    echo "<h2 style='color:red; align-items:center;'>No stock for this category</h2>";
                }
            while($row = mysqli_fetch_assoc($result_category)){
            $product_id = $row['product_id'];
            $product_name = htmlspecialchars($row['product_name']);
            $product_image = htmlspecialchars($row['product_image']);
            $product_price = htmlspecialchars($row['product_price']);
            $category_id = $row['category_id'];
            $stock = htmlspecialchars($row['stock_quantity']);
            echo "
            <div class='product-card'>
            <img class='product-card-image' src='./Artisans/images/$product_image' alt='$product_name'>
            <div class='product-card-info'>
            <h4>$product_name</h4>
            <p>ksh $product_price</p>
            <p>$stock items left</p>
            <a href='index.php?add_to_cart=" . (int)$product_id . "' class='add-to-cart'>Add to cart</a>
            </div>
            </div>";
            }    
        }
     }
     //search products function
     function search_product(){
        global $conn;
        if(isset($_GET['search_data_products'])){
        $search_data_value = $_GET['search_data'];
        $search_query = "select * from products where product_keywords like ? and approval_status = 'approved'";
        $stmt = mysqli_prepare($conn, $search_query);
        $like_value = '%' . $search_data_value . '%';
        mysqli_stmt_bind_param($stmt, "s", $like_value);
        mysqli_stmt_execute($stmt);
        $result_query = mysqli_stmt_get_result($stmt);
        $num_of_rows = mysqli_num_rows($result_query);
        if($num_of_rows == 0){
            echo "<h2 style='color:red; text-align:center;'>No result match. No products found on this category</h2>";
        }
        while($row = mysqli_fetch_assoc($result_query)){
            $product_id = $row['product_id'];
            $product_name = htmlspecialchars($row['product_name']);
            $product_image = htmlspecialchars($row['product_image']);
            $product_price = htmlspecialchars($row['product_price']);
            $category_id = $row['category_id'];
            $stock = htmlspecialchars($row['stock_quantity']);
            echo "
            <div class='product-card'>
            <img class='product-card-image' src='./Artisans/images/$product_image' alt='$product_name'>
            <div class='product-card-info'>
            <h4>$product_name</h4>
            <p>ksh $product_price</p>
            <p>$stock items left</p>
            <a href='index.php?add_to_cart=" . (int)$product_id . "' class='add-to-cart'>Add to cart</a>
            </div>
            </div>";
        
        }
        }
     }
    
      //cart function
    function cart(){
     if(isset($_GET['add_to_cart'])){
        global $conn;
        $get_ip_address = getIPAddress();
        $get_product_id = (int) $_GET['add_to_cart'];

        // Don't let anyone add an out-of-stock or non-approved product to
        // their cart - closes the direct-URL bypass (?add_to_cart=ID) that
        // would otherwise skip the display filter entirely.
        $stock_stmt = mysqli_prepare($conn, "SELECT stock_quantity, approval_status FROM products WHERE product_id = ?");
        mysqli_stmt_bind_param($stock_stmt, "i", $get_product_id);
        mysqli_stmt_execute($stock_stmt);
        $stock_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stock_stmt));
        mysqli_stmt_close($stock_stmt);

        if (!$stock_row || $stock_row['approval_status'] !== 'approved') {
            echo "<script>alert('This item is not currently available.')</script>";
            echo "<script>window.open('index.php','_self')</script>";
            return;
        }

        if ($stock_row['stock_quantity'] <= 0) {
            echo "<script>alert('Sorry, this item is out of stock.')</script>";
            echo "<script>window.open('index.php','_self')</script>";
            return;
        }

        // Atomic upsert: relies on the UNIQUE KEY on (ip_address, product_id).
        // If this IP already has a row for this product, bump the quantity
        // instead of inserting a duplicate row - this replaces the old
        // SELECT-then-INSERT check, which had a race condition and also
        // broke once product_id alone was the table's PRIMARY KEY (that
        // caused the "Duplicate entry '7' for key 'PRIMARY'" error, since
        // it blocked a second customer from ever adding product 7 to
        // their own cart).
        $insert_stmt = mysqli_prepare($conn,
            "INSERT INTO cart_items (product_id, ip_address, quantity, added_at)
             VALUES (?, ?, 1, NOW())
             ON DUPLICATE KEY UPDATE quantity = quantity + 1"
        );
        mysqli_stmt_bind_param($insert_stmt, "is", $get_product_id, $get_ip_address);
        mysqli_stmt_execute($insert_stmt);
        mysqli_stmt_close($insert_stmt);
        echo "<script>alert('Item added to the cart')</script>";
        echo "<script>window.open('index.php','_self')</script>";
     }
    }
    //function to get cart item numbers
    function cart_item(){
        global $conn;
        $get_ip_address = getIPAddress();
        $stmt = mysqli_prepare($conn, "SELECT * FROM cart_items WHERE ip_address = ?");
        mysqli_stmt_bind_param($stmt, "s", $get_ip_address);
        mysqli_stmt_execute($stmt);
        $count_cart_items = mysqli_num_rows(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        echo $count_cart_items;
    }
    //total price function
    function total_cart_price(){
        global $conn;
        $get_ip_address = getIPAddress();
        $total_price = 0;

        $cart_stmt = mysqli_prepare($conn, "SELECT product_id, quantity FROM cart_items WHERE ip_address = ?");
        mysqli_stmt_bind_param($cart_stmt, "s", $get_ip_address);
        mysqli_stmt_execute($cart_stmt);
        $cart_result = mysqli_stmt_get_result($cart_stmt);

        while($row = mysqli_fetch_assoc($cart_result)){
            $product_id = $row['product_id'];
            $quantity   = $row['quantity'];

            $price_stmt = mysqli_prepare($conn, "SELECT product_price FROM products WHERE product_id = ?");
            mysqli_stmt_bind_param($price_stmt, "i", $product_id);
            mysqli_stmt_execute($price_stmt);
            $price_row = mysqli_fetch_assoc(mysqli_stmt_get_result($price_stmt));
            mysqli_stmt_close($price_stmt);

            if ($price_row) {
                // NOTE: this now multiplies by quantity — the original
                // version ignored quantity entirely and always priced
                // each cart line as if quantity was 1.
                $total_price += $price_row['product_price'] * $quantity;
            }
        }
        mysqli_stmt_close($cart_stmt);
        echo $total_price;
    }   
        //getting customer order details
    function customer_order_details(){
        global $conn;
        $username = $_SESSION['username'];

        $stmt = mysqli_prepare($conn, "SELECT customer_id FROM customers WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result_query = mysqli_stmt_get_result($stmt);

        while($row_query = mysqli_fetch_assoc($result_query))
            {
                $customer_id = $row_query['customer_id'];
                if(!isset($_GET['edit_account'])){
                    if(!isset($_GET['my_orders'])){
                        if(!isset($_GET['delete_account'])){
                            $orders_stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE customer_id = ? AND order_status = 'pending'");
                            mysqli_stmt_bind_param($orders_stmt, "i", $customer_id);
                            mysqli_stmt_execute($orders_stmt);
                            $row_count = mysqli_num_rows(mysqli_stmt_get_result($orders_stmt));
                            mysqli_stmt_close($orders_stmt);

                            if($row_count > 0){
                                echo "<h3 style='color:green; text-align: center;'>You have <span class='text-danger'>$row_count</span> pending orders</h3>
                                <p class='text-center'><a href='profile.php?my_orders' class='text-dark'>Order Details</a></p>";
                            }
                            else {
                                echo "<h3 class='text-center text-info'>You have zero pending orders</h3>
                                <p class='text-center'><a href='../index.php' class='text-dark'>Explore products</a></p>";
                            }
                        }
                    }
                }
            }
        mysqli_stmt_close($stmt);
    }
    //handles cross site request forgery
    function csrf_token(){
        if(empty($_SESSION['csrf_token']))
            {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
    }
    function csrf_field()
    {
        echo '<input type ="hidden" name="csrf_token" value="'.htmlspecialchars(csrf_token()).'"/>';
    }
    function csrf_verify(){
        if(empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])){
            echo "<script>alert('Invalid session. Please refresh and try again.')</script>";
            exit;
        }
    }

    ?>