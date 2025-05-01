<?php
include("../includes/dbconnect.php");
session_start();
date_default_timezone_set("Asia/Calcutta");

// If already logged in, redirect to admin home
if(isset($_SESSION['admin_id'])) {
    header("Location: adminhome.php");
    exit();
}

if(isset($_POST['btn'])) {
    $uname = mysqli_real_escape_string($conn, $_POST['uname']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $qry = mysqli_query($conn, "SELECT * FROM admin WHERE name='$uname' && psw='$password'");
    $num = mysqli_num_rows($qry);
    
    if($num == 1) {
        $admin = mysqli_fetch_assoc($qry);
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['name'];
        
        echo "<script>alert('Welcome to admin home page');</script>";
        header("Location: adminhome.php");
    } else {
        echo "<script>alert('User Name Password Wrong.....')</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Vel Power Tools Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
			min-height:300px;
        }
        .login-header {
            background: #1d3557;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .login-body {
            padding: 30px;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #1d3557;
        }
        .btn-login {
            background: #1d3557;
            color: white;
            width: 100%;
            padding: 12px;
        }
        .btn-login:hover {
            background: #152a45;
            color: white;
        }
        .input-group-text {
            background: #1d3557;
            color: white;
            border: none;
        }
        .current-time {
            text-align: center;
            color: #6c757d;
            margin-top: 15px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
           <a href="../index.php" style="text-decoration:none;color:white"><h4 class="mb-0"><i class="fas fa-tools me-2"></i>Vel Power Tools Admin</h4></a> 
        </div>
        
        <div class="login-body">
            <form method="POST">
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" class="form-control" name="uname" placeholder="Username" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>
                </div>
                
                <button type="submit" name="btn" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
            
            <div class="current-time">
                <?php echo date('Y-m-d H:i:s'); ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>