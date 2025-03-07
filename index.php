<?php
session_start();
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .navbar {
            background: #333;
            color: #fff;
            padding: 15px;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            width: 100%;
        }

        .navbar .logo {
            font-size: 30px;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
        }

        .navbar .nav-links {
            list-style: none;
            display: flex;
            align-items: center;
        }

        .navbar .nav-links li {
            margin-left: 20px;
            position: relative;
        }

        .navbar .nav-links a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            transition: background 0.3s;
        }

        .navbar .nav-links a:hover {
            background: #555;
        }

        .navbar .dropdown {
            position: relative;
        }

        .navbar .dropdown-menu {
            display: none;
            position: absolute;
            background-color: #fff;
            min-width: 150px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            margin-top: 10px;
            list-style: none;
            padding: 10px 0;
            border-radius: 5px;
            right: 0;
        }

        .navbar .dropdown-menu li {
            margin: 0;
        }

        .navbar .dropdown-menu li a {
            color: #333;
            padding: 10px 20px;
            text-decoration: none;
            display: block;
            transition: background 0.3s;
        }

        .navbar .dropdown-menu li a:hover {
            background: #f4f4f4;
        }

        .navbar .dropdown:hover .dropdown-menu {
            display: block;
        }

        .navbar .btn {
            background-color: #28a745; 
            color: #fff; 
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.3s;
        }

        .navbar .btn:hover {
            background-color: #218838; /
            transform: scale(1.05); /
        }

        .navbar .dropdown {
            position: relative;
        }

        .navbar .dropdown-menu {
            display: none;
            position: absolute;
            background-color: #fff;
            min-width: 150px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            margin-top: 10px;
            list-style: none;
            padding: 10px 0;
            border-radius: 5px;
            right: 0;
        }

        .navbar .dropdown-menu li {
            margin: 0;
        }

        .navbar .dropdown-menu li a {
            color: #333;
            padding: 10px 20px;
            text-decoration: none;
            display: block;
            transition: background 0.3s;
        }

        .navbar .dropdown-menu li a:hover {
            background: #f4f4f4;
        }

        .navbar .dropdown:hover .dropdown-menu {
            display: block;
        }

        .hero {
            background: url('home.jpg') no-repeat center center/cover;
            height: 100vh;
            display: flex;
            align-items: center;
            color: #fff;
            position: relative;
            text-align: center;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.2); 
        }

        .hero .container {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 20px;
            margin-bottom: 30px;
        }

        .about {
            padding: 50px 20px;
            background: #fff;
            text-align: center;
        }

        .about h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .about p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto;
        }

        .services {
            padding: 50px 20px;
            background: #f9f9f9;
        }

        .services h2 {
            font-size: 36px;
            text-align: center;
            margin-bottom: 40px;
        }

        .services .service-cards {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .services .card {
            background: #fff;
            padding: 20px;
            margin: 10px;
            flex: 1;
            max-width: 300px;
            text-align: center;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .services .card:hover {
            transform: translateY(-5px);
        }

        .services .card h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .services .card p {
            font-size: 16px;
            color: #666;
        }

        .departments {
            padding: 50px 20px;
            background: #fff;
        }

        .departments h2 {
            font-size: 36px;
            text-align: center;
            margin-bottom: 40px;
        }

        .departments .department-list {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .departments .department {
            background: #28a745;
            color: #fff;
            padding: 20px;
            margin: 10px;
            flex: 1;
            max-width: 300px;
            text-align: center;
            border-radius: 5px;
            transition: transform 0.3s;
        }

        .departments .department:hover {
            transform: translateY(-5px);
        }

        .departments .department h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .departments .department p {
            font-size: 16px;
        }

        .contact {
            padding: 50px 20px;
            background: #f9f9f9;
            text-align: center;
        }

        .contact h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .contact p {
            font-size: 18px;
            margin-bottom: 30px;
        }

        .footer {
            background: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }

        .footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="#" class="logo">Hospital Management System</a>
            <ul class="nav-links">
                <li><a href="#">Home</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#departments">Departments</a></li>
                <li><a href="#contact">Contact</a></li>
                <li class="dropdown">
                    <a href="#" class="btn">Login</a>
                    <ul class="dropdown-menu">
                        <li><a href="admin_login.php">Admin Login</a></li>
                        <li><a href="doctor_login.php">Doctor Login</a></li>
                        <li><a href="patient_login.php">Patient Login</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    <header class="hero">
        <div class="container">
            <h1>Welcome to Our Hospital</h1>
            <p>Providing Quality Health Services for You and Your Family</p>
        </div>
    </header>

    <!-- About Us Section -->
    <section id="about" class="about">
        <div class="container">
            <h2>About Us</h2>
            <p>Our hospital is committed to providing excellent healthcare services to our community. We have a team of dedicated professionals working round the clock to ensure the best care for our patients.</p>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <h2>Our Services</h2>
            <div class="service-cards">
                <div class="card">
                    <h3>Emergency Care</h3>
                    <p>Immediate and effective treatment for urgent health conditions.</p>
                </div>
                <div class="card">
                    <h3>Outpatient Services</h3>
                    <p>Comprehensive care for non-admitted patients.</p>
                </div>
                <div class="card">
                    <h3>Surgical Procedures</h3>
                    <p>Advanced surgical care with cutting-edge technology.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Departments Section -->
    <section id="departments" class="departments">
        <div class="container">
            <h2>Our Departments</h2>
            <div class="department-list">
                <div class="department">
                    <h3>Cardiology</h3>
                    <p>Specialized care for heart-related conditions.</p>
                </div>
                <div class="department">
                    <h3>Neurology</h3>
                    <p>Advanced treatment for neurological disorders.</p>
                </div>
                <div class="department">
                    <h3>Pediatrics</h3>
                    <p>Quality healthcare for children and adolescents.</p>
                </div>
                <div class="department">
                    <h3>Orthopedics</h3>
                    <p>Comprehensive care for bone, joint, and muscle conditions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Us Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2>Contact Us</h2>
            <p>If you have any questions or need assistance, feel free to reach out to us.</p>
            <p>Email: contact@hospital.com | Phone: +123 456 7890</p>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Hospital Management System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
