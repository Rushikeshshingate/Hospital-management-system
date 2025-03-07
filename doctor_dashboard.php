<?php
session_start();
include('db_connection.php'); 

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'doctor') {
    header("Location: doctor_login.php");
    exit();
}

$doctor_id = $_SESSION['doctor_id'];
$queryPatients = "SELECT COUNT(*) as total_patients FROM medical_records WHERE doctor_id = '$doctor_id'";
$resultPatients = mysqli_query($conn, $queryPatients);

$totalPatients = mysqli_fetch_assoc($resultPatients)['total_patients'];

$queryDoctor = "SELECT name FROM doctors WHERE id = '$doctor_id'";
$resultDoctor = mysqli_query($conn, $queryDoctor);
$doctor = mysqli_fetch_assoc($resultDoctor);
$doctorName = $doctor['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            color: #333;
        }
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            width: 210px;
            position: fixed; 
            transition: all 0.3s;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px;
            display: block;
            font-size: 1.3rem; 
            white-space: nowrap; 
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            margin-left: 200px; 
            padding: 20px;
            width: calc(100% - 200px); 
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            background: white;
            z-index: 1000;
            padding: 10px 20px;
            border-bottom: 1px solid #dee2e6;
        }
        .dashboard-header h1 {
            margin: 0;
            font-size: 2rem;
            color: #007bff;
        }
        .welcome-message {
            font-size: 2.5rem; 
            color: #343a40;
            margin-top: 30px;
            text-align: center;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 30px; 
            margin-top: 40px;
        }
        .dashboard-grid a {
            text-decoration: none;
            color: inherit;
        }
        .dashboard-grid .card {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .dashboard-grid .card .card-body {
            text-align: center;
            padding: 20px;
        }
        .dashboard-grid .card .card-body i {
            font-size: 3rem; 
            margin-bottom: 10px;
            color: #007bff;
        }
        .dashboard-grid .card .card-header {
            background-color: #f8f9fa;
            padding: 15px;
            font-weight: bold;
            border-bottom: 1px solid #dee2e6;
            text-align: center;
        }
        .top-cards {
            display: flex;
            justify-content: center; 
            gap: 30px; 
            margin-bottom: 30px; 
        }
        .additional-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr); 
            gap: 30px;
            justify-content: center; 
            margin-top: 50px;
            max-width: 960px; 
            margin-left: auto; 
            margin-right: auto; 
        }
        .additional-cards .card {
            height: 110px; 
            width: 80%;
            display: flex;
            flex-direction: column;
            justify-content: center; 
            align-items: center; 
            text-align: center; 
            margin: auto; 
        }
        .btn-group .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-3">
            <a href="doctor_dashboard.php"><i class="fas fa-building"></i> Dashboard</a>
            <a href="medical_record.php"><i class="fas fa-user-injured"></i> Medical Record </a>
            <a href="prescription.php"><i class="fas fa-prescription"></i> Prescriptions</a>
            <a href="setting.php"><i class="fas fa-cogs"></i> Settings</a>
        </div>

        <div class="main-content">
            <div class="dashboard-header mb-4">
                <h1>Doctor Dashboard</h1>
                <div class="btn-group">
                    <button class="btn btn-primary">Doctor Panel</button>
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Account
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                        <li><a class="dropdown-item" href="setting.php">Profile</a></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="welcome-message">
                Welcome, Dr. <?php echo htmlspecialchars($doctorName); ?>!
            </div>

            <div class="dashboard-grid top-cards">
                <div class="card">
                    <div class="card-header">My Patients</div>
                    <div class="card-body">
                        <i class="fas fa-user-injured"></i>
                        <h5 class="card-title">Total Patients</h5>
                        <p class="card-text"><?php echo $totalPatients; ?></p>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid additional-cards">
                <a href="medical_record.php" class="card">
                    <div class="card-body">
                        <i class="fas fa-user-injured"></i>
                        <h5 class="card-title">Medical Records</h5>
                    </div>
                </a>

                <a href="prescription.php" class="card">
                    <div class="card-body">
                        <i class="fas fa-prescription"></i>
                        <h5 class="card-title">Prescriptions</h5>
                    </div>
                </a>

                <a href="setting.php" class="card">
                    <div class="card-body">
                        <i class="fas fa-cogs"></i>
                        <h5 class="card-title">Settings</h5>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
