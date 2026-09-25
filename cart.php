<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
include('database_connection.php');
include('functions/sharedfunctions.php');

$get_ip_add = getIPAddress();
//updates cart quantity
if (isset($_POST['update_cart'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity   = (int) $_POST['qty'];
    if ($quantity < 1) {
        $quantity = 1;
    }
    $update_stmt = mysqli_prepare($conn, "UPDATE cart_items SET quantity = ? WHERE product_id = ? AND ip_address = ?");
    mysqli_stmt_bind_param($update_stmt, "iis", $quantity, $product_id, $get_ip_add);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
    header("Location: cart.php");
    exit;
}

// deletes cart item
if (isset($_POST['remove_cart'])) {
    $product_id = (int) $_POST['product_id'];
    $delete_stmt = mysqli_prepare($conn, "DELETE FROM cart_items WHERE product_id = ? AND ip_address = ?");
    mysqli_stmt_bind_param($delete_stmt, "is", $product_id, $get_ip_add);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
    header("Location: cart.php");
    exit;
}

// ---- Fetch cart items joined with product details in a single query ----
$cart_stmt = mysqli_prepare($conn, "SELECT c.product_id, c.quantity, c.added_at, p.product_name, p.product_price, p.product_image
                                     FROM cart_items c
                                     JOIN products p ON c.product_id = p.product_id
                                     WHERE c.ip_address = ?
                                     ORDER BY c.added_at DESC");
mysqli_stmt_bind_param($cart_stmt, "s", $get_ip_add);
mysqli_stmt_execute($cart_stmt);
$cart_result = mysqli_stmt_get_result($cart_stmt);

$cart_items  = [];
$total_price = 0;
$total_items = 0;

while ($row = mysqli_fetch_assoc($cart_result)) {
    $row['line_total'] = $row['product_price'] * $row['quantity'];
    $total_price += $row['line_total'];
    $total_items += $row['quantity'];
    $cart_items[] = $row;
}
mysqli_stmt_close($cart_stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | ArtisanOrders</title>

    <!--font awesome link-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!--Css file-->
    <link rel="stylesheet" href="style.css">

    <style>
        body{background: #E8F0F3}
        :root {
            --navy: #14213d;
            --navy-soft: #2a3a5c;
            --teal: #0f8b8d;
            --teal-dark: #0b6b6d;
            --teal-tint: #e7f6f6;
            --border: #e2e6ea;
            --text-muted: #6b7280;
            --danger: #c1443a;
            --radius: 8px;
        }

        /* ---------- Top bar ---------- */
        header.site-header {
            background: #fff;
            border-bottom: 1px solid var(--border);
        }
        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 32px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand img { height: 44px; }
        .brand h1 { font-size: 1.15rem; color: var(--navy); margin: 0; }
        .brand p { font-size: 0.8rem; color: var(--text-muted); margin: 0; }
        .account-bar {
            display: flex;
            align-items: center;
            gap: 18px;
            font-size: 0.9rem;
            color: var(--navy-soft);
        }
        .account-bar a { color: var(--teal-dark); text-decoration: none; font-weight: 600; }
        .account-bar a:hover { text-decoration: underline; }

        /* ---------- Page heading ---------- */
        .cart-page {
            max-width: 1000px;
            margin: 0 auto;
            padding: 32px 24px 64px;
        }
        .cart-page > h2 {
            font-size: 1.5rem;
            color: var(--navy);
            margin: 0 0 4px;
        }
        .cart-page > p.item-count {
            color: var(--text-muted);
            margin: 0 0 24px;
            font-size: 0.92rem;
        }

        /* ---------- Cart table ---------- */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        .cart-table thead th {
            background: var(--navy);
            color: #fff;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            padding: 14px 16px;
        }
        .cart-table tbody td {
            padding: 16px;
            border-top: 1px solid var(--border);
            vertical-align: middle;
            font-size: 0.95rem;
            color: var(--navy-soft);
        }
        .cart-table tbody tr:hover { background: #fafbfc; }

        .product-cell { display: flex; align-items: center; gap: 14px; }
        .product-cell img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--border);
        }
        .product-cell span { font-weight: 600; color: var(--navy); }

        .qty-form { display: flex; align-items: center; gap: 8px; }
        .qty-stepper {
            display: flex;
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 6px;
            overflow: hidden;
        }
        .qty-stepper button {
            background: var(--teal-tint);
            border: none;
            width: 30px;
            height: 32px;
            font-size: 1rem;
            color: var(--teal-dark);
            cursor: pointer;
        }
        .qty-stepper button:hover { background: var(--teal); color: #fff; }
        .qty-stepper input {
            width: 42px;
            height: 32px;
            border: none;
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border);
            text-align: center;
            font-size: 0.95rem;
            -moz-appearance: textfield;
        }
        .qty-stepper input::-webkit-outer-spin-button,
        .qty-stepper input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .link-btn {
            background: none;
            border: none;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
        }
        .link-btn.update { color: var(--teal-dark); }
        .link-btn.update:hover { text-decoration: underline; }
        .link-btn.remove { color: var(--danger); }
        .link-btn.remove:hover { text-decoration: underline; }

        .line-total { font-weight: 700; color: var(--navy); white-space: nowrap; }

        /* ---------- Summary card ---------- */
        .cart-summary {
            max-width: 340px;
            margin: 28px 0 0 auto;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 22px;
        }
        .cart-summary .row {
            display: flex;
            justify-content: space-between;
            font-size: 0.92rem;
            color: var(--navy-soft);
            padding: 6px 0;
        }
        .cart-summary .row.total {
            border-top: 1px solid var(--border);
            margin-top: 8px;
            padding-top: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--navy);
        }
        .cart-summary .row.total span:last-child { color: var(--teal-dark); }

        .cart-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 16px;
        }
        .btn-artisan {
            display: block;
            width: 100%;
            padding: 12px 20px;
            font-size: 0.95rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            box-sizing: border-box;
        }
        .btn-primary { background: var(--teal); color: #fff; }
        .btn-primary:hover { background: var(--teal-dark); }
        .btn-secondary { background: #fff; color: var(--navy); border: 1px solid var(--border); }
        .btn-secondary:hover { border-color: var(--navy); }

        /* ---------- Empty state ---------- */
        .cart-empty {
            text-align: center;
            padding: 64px 24px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }
        .cart-empty i { font-size: 2.6rem; color: var(--teal); margin-bottom: 14px; }
        .cart-empty h3 { color: var(--navy); margin: 0 0 6px; }
        .cart-empty p { color: var(--text-muted); margin: 0 0 22px; }
        .cart-empty .btn-artisan { max-width: 220px; margin: 0 auto; }

        /* ---------- Responsive ---------- */
        @media (max-width: 720px) {
            .cart-table thead { display: none; }
            .cart-table, .cart-table tbody, .cart-table tr, .cart-table td { display: block; width: 100%; }
            .cart-table tbody tr {
                border: 1px solid var(--border);
                border-radius: var(--radius);
                margin-bottom: 14px;
                padding: 8px;
            }
            .cart-table tbody td {
                border-top: none;
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 10px;
            }
            .cart-table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--text-muted);
                font-size: 0.78rem;
                text-transform: uppercase;
                margin-right: 12px;
            }
            .product-cell { justify-content: flex-end; }
            .cart-summary { max-width: 100%; }
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
        <aside>
        <span class="useraccount"><i class="fa-solid fa-user"></i></span>
        <p style="padding:5px;">
        <a href="customers/login.php">Login</a> | <a href="customers/registration.php">Register</a><br>
        <strong><a href="Artisans/registration.php">Become a seller</a></strong> | <strong><a href="Artisans/login.php">Login to seller</a></strong>
        </p>
        <p style="display:flex; align-items:center; gap:8px;font-weight:600;">
            <a href="cart.php"><i class="fa fa-shopping-cart" style="font-size:28px"></i><sup><?php cart_item(); ?></sup></a> 
        </p>
       </aside>
    </header>


<main class="cart-page">
    <h2>Your Cart</h2>

    <?php if (count($cart_items) === 0): ?>

        <div class="cart-empty">
            <i class="fa-solid fa-cart-shopping"></i>
            <h3>Your cart is empty</h3>
            <p>Browse our artisan collection and add something you love.</p>
            <a href="./index.php" class="btn-artisan btn-primary">Start Shopping</a>
        </div>

    <?php else: ?>

        <p class="item-count"><?php echo $total_items; ?> item<?php echo $total_items === 1 ? '' : 's'; ?> in your cart</p>

        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td data-label="Product">
                        <div class="product-cell">
                            <img src="./Artisans/images/<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                            <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                        </div>
                    </td>
                    <td data-label="Price">Ksh <?php echo number_format($item['product_price'], 2); ?></td>
                    <td data-label="Quantity">
                        <form class="qty-form" method="POST" action="cart.php">
                            <input type="hidden" name="product_id" value="<?php echo (int) $item['product_id']; ?>">
                            <div class="qty-stepper">
                                <button type="button" onclick="stepQty(this, -1)" aria-label="Decrease quantity">&minus;</button>
                                <input type="number" name="qty" min="1" value="<?php echo (int) $item['quantity']; ?>">
                                <button type="button" onclick="stepQty(this, 1)" aria-label="Increase quantity">&plus;</button>
                            </div>
                            <button type="submit" name="update_cart" class="link-btn update">Update</button>
                        </form>
                    </td>
                    <td data-label="Subtotal" class="line-total">Ksh <?php echo number_format($item['line_total'], 2); ?></td>
                    <td data-label="">
                        <form method="POST" action="cart.php" onsubmit="return confirm('Remove this item from your cart?');">
                            <input type="hidden" name="product_id" value="<?php echo (int) $item['product_id']; ?>">
                            <button type="submit" name="remove_cart" class="link-btn remove">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-summary">
            <div class="row">
                <span>Items (<?php echo $total_items; ?>)</span>
                <span>Ksh <?php echo number_format($total_price, 2); ?></span>
            </div>
            <div class="row total">
                <span>Total</span>
                <span>Ksh <?php echo number_format($total_price, 2); ?></span>
            </div>
            <div class="cart-actions">
                <a href="./customers/checkout.php" class="btn-artisan btn-primary">Checkout</a>
                <a href="./index.php" class="btn-artisan btn-secondary">Continue Shopping</a>
            </div>
        </div>

    <?php endif; ?>
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

<script>
function stepQty(btn, delta) {
    const wrapper = btn.closest('.qty-stepper');
    const input = wrapper.querySelector('input[name="qty"]');
    let value = parseInt(input.value, 10) || 1;
    value = Math.max(1, value + delta);
    input.value = value;
}
</script>

</body>
</html>