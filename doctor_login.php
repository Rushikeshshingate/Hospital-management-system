<?php
session_start();
include('db_connection.php');

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $email_search = "SELECT * FROM doctors WHERE email='$email'";
    $query = mysqli_query($conn, $email_search);

    $email_count = mysqli_num_rows($query);

    if ($email_count == 1) {
        $row = mysqli_fetch_assoc($query);
        $role = $row['role']; 
        $hashed_password = $row['password'];  

        if (password_verify($password, $hashed_password)) {
            if ($role === 'doctor') {
                $_SESSION['email'] = $email;
                $_SESSION['user_role'] = $role; 
                $_SESSION['doctor_id'] = $row['id']; 
                header('Location: doctor_dashboard.php');
                exit();
            } else {
                $error_message = "Access denied. Only doctors can log in.";
            }
        } else {
            $error_message = "Password is invalid";
        }
    } else {
        $error_message = "Email is invalid";
    }
}

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'doctor') {
    header("Location: doctor_dashboard.php"); 
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Login - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('login.png');
            background-size: cover; 
            background-position: center; 
            background-repeat: no-repeat; 
            height: 100vh; 
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.9); 
            padding: 40px;
            border-radius: 10px; 
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
        }

        .form-label {
            color: #000; 
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
        }

        .btn-primary {
        background-color: #28a745 !important; 
        border-color: #28a745 !important;
        font-weight: bold;
        }

        .btn-primary:hover {
            background-color: #218838 !important; 
            border-color: #218838 !important;
        }

        .btn-primary:focus, .btn-primary:active {
            background-color: #218838 !important; 
            border-color: #218838 !important;
            box-shadow: none !important; 
            outline: none !important; 
        }

        .alert-danger {
            margin-bottom: 20px;
        }
        @media (max-width: 576px) {
            .login-container {
                padding: 20px;
            }

            h2 {
                font-size: 20px;
            }

            .btn-primary {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Doctor Login</h2>
        <?php
        if (isset($error_message)) {
            echo '<div class="alert alert-danger" role="alert">' . $error_message . '</div>';
        }
        ?>
        <form action="" method="POST">
            <div class="mb-3">
                <label for="doctor_email" class="form-label">Doctor Email</label>
                <input type="email" class="form-control" id="doctor_email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
