<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(isset($_GET['suspend_product']))
    {
        // Must be logged in as an admin
        if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo "<script>alert('Access denied.')</script>";
            echo "<script>window.location.href='../Artisans/login.php';</script>";
            exit;
        }

        //csrf token generation
        csrf_token();

        if (isset($_POST['confirm_suspend'])) {
            //verify csrf token
            csrf_verify();

            $product_id = (int)($_POST['product_id'] ?? 0);
            if ($product_id <= 0) {
                echo "<script>alert('Invalid product.')</script>";
                exit;
            }

            $update_stmt = mysqli_prepare($conn, "update products set approval_status = 'suspended' where product_id = ?");
            mysqli_stmt_bind_param($update_stmt, "i", $product_id);
            $ok = mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);

            if ($ok) {
                echo "<script>alert('Product suspended and removed from the site.');window.location.href='index.php?view_products';</script>";
            } else {
                echo "<script>alert('Update failed. Please try again.');window.location.href='index.php?view_products';</script>";
            }
            exit;
        }

        // GET with a product_id shows a confirm form
        $product_id = (int)($_GET['product_id'] ?? 0);
        if ($product_id <= 0) {
            echo "<script>alert('Invalid product.');window.location.href='index.php?view_products';</script>";
            exit;
        }

        $product_stmt = mysqli_prepare($conn, "select product_id, product_name from products where product_id = ?");
        mysqli_stmt_bind_param($product_stmt, "i", $product_id);
        mysqli_stmt_execute($product_stmt);
        $product_row = mysqli_fetch_assoc(mysqli_stmt_get_result($product_stmt));

        if (!$product_row) {
            echo "<script>alert('Product not found.');window.location.href='index.php?view_products';</script>";
            exit;
        }
?>
        <div style="max-width:480px;margin:30px auto;background:#f8f9fa;padding:24px;border-radius:8px;text-align:center;">
            <p>Suspend "<?php echo htmlspecialchars($product_row['product_name']); ?>"? It will stop showing on the site immediately.</p>
            <form method="post" action="index.php?suspend_product">
                <?php csrf_field(); ?>
                <input type="hidden" name="product_id" value="<?php echo (int)$product_row['product_id']; ?>"/>
                <button type="submit" name="confirm_suspend" style="background:#a11;color:white;border:none;padding:10px 24px;border-radius:6px;cursor:pointer;">Confirm Suspend</button>
                <a href="index.php?view_products" style="margin-left:12px;">Cancel</a>
            </form>
        </div>
<?php
    }
?>