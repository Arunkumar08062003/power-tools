<?php
include("../includes/dbconnect.php");
session_start();
date_default_timezone_set("Asia/Calcutta");

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if(isset($_GET['id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Get product image before deletion
    $query = "SELECT image FROM products WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $query);
    $product = mysqli_fetch_assoc($result);
    
    // Delete the product
    $delete_query = "DELETE FROM products WHERE product_id = '$product_id'";
    
    if(mysqli_query($conn, $delete_query)) {
        // Delete product image if exists
        if($product['image'] && file_exists("../uploads/products/" . $product['image'])) {
            unlink("../uploads/products/" . $product['image']);
        }
        
        echo "<script>
            alert('Product deleted successfully!');
            window.location.href='products.php';
        </script>";
    } else {
        echo "<script>
            alert('Error deleting product!');
            window.location.href='products.php';
        </script>";
    }
} else {
    header("Location: products.php");
}
?>