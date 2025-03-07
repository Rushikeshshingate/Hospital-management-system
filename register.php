<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $mobile = (int)$_POST['mobile']; 
    $age = (int)$_POST['age'];      
    $address = $conn->real_escape_string($_POST['address']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    $sql = "INSERT INTO patients_registration (name, email, age, address, mobile, password)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssisss", $name, $email, $age, $address, $mobile, $password);

        if ($stmt->execute()) {
            header("Location: patient_login.php");
            exit();
        } else {
            $error = "Error registering user. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Error preparing the SQL statement. Please try again.";
    }

    if (isset($error)) {
        echo '<div class="alert alert-danger">' . $error . '</div>';
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Patient - Hospital Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('registration.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            max-width: 600px;
            background-color: rgba(255, 255, 255, 0.8); 
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }

        header h1 {
            text-align: center;
            color: #333;
            margin-top: 150px;
            margin-bottom: 20px;
        }

        .top-nav {
            text-align: center;
        }

        .top-nav a {
            color: #007bff;
            text-decoration: none;
            margin: 0 10px;
        }

        .form-right {
            text-align: center;
        }

        .form-right h2 {
            color: #333;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            text-align: left;
            color: #555;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            padding: 10px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .alert {
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
        }

        p {
            font-size: 14px;
        }

        p a {
            color: #28a745;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .container {
                max-width: 100%;
                padding: 20px;
                margin: 20px;
            }

            input[type="text"],
            input[type="email"],
            input[type="password"],
            input[type="number"] {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>HOSPITAL MANAGEMENT SYSTEM</h1>
            <nav class="top-nav">
                <a href="index.php">Home</a>
            </nav>
        </header>
        <main>
            <div class="form-right">
                <h2>Patient Registration</h2>
                <?php if (isset($error)) { echo '<div class="alert alert-danger">' . $error . '</div>'; } ?>
                <form action="" method="POST">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>

                    <label for="mobile">Mobile Number</label>
                    <input type="number" id="mobile" name="mobile" min="1000000000" max="9999999999" required>

                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" min="0" max="120" required>

                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" required>

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>

                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>

                    <button type="submit">Register</button>
                </form>
                <p>Already have an account? <a href="patient_login.php">Login here</a></p>
            </div>
        </main>
    </div>
</body>
</html>
