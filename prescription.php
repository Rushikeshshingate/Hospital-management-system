<?php
session_start();
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['doctor', 'patient', 'admin'])) {
    header("Location: index.php");
    exit();
}

include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['user_role'] == 'doctor') {
    $patient_id = $_POST['patient_id'];
    $prescription_details = $_POST['prescription_details'];
    $doctor_id = $_SESSION['doctor_id']; 

    if (!empty($patient_id) && !empty($prescription_details)) {
        $stmt = $conn->prepare("INSERT INTO prescriptions (patient_id, doctor_id, prescription, date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $patient_id, $doctor_id, $prescription_details);
        if ($stmt->execute()) {
            $success_message = "Prescription added successfully.";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Please fill in all fields.";
    }
}

$prescriptions = [];
if ($_SESSION['user_role'] == 'patient') {
    $patient_id = $_SESSION['patient_id'];
    $stmt = $conn->prepare("SELECT p.id, d.name AS doctor_name, p.prescription, p.date 
                            FROM prescriptions p
                            JOIN doctors d ON p.doctor_id = d.id
                            WHERE p.patient_id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $prescriptions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch all patients for the doctor's department using medical_records table
$patients = [];
if ($_SESSION['user_role'] == 'doctor') {
    $doctor_id = $_SESSION['doctor_id']; 

    // Fetch the department ID of the logged-in doctor
    $stmt = $conn->prepare("SELECT department_id FROM doctors WHERE id = ?");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $stmt->bind_result($department_id);
    $stmt->fetch();
    $stmt->close();

    // Fetch patients only from the doctor's department using the medical_records table
    $stmt = $conn->prepare("
        SELECT pr.id, pr.name 
        FROM patients_registration pr
        JOIN medical_records mr ON pr.id = mr.patient_id
        WHERE mr.department_id = ?");
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $patients = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Fetch all prescriptions for the doctor
    $doctor_prescriptions = [];
    $stmt = $conn->prepare("SELECT p.id, pa.name AS patient_name, p.prescription, p.date 
                            FROM prescriptions p
                            JOIN patients_registration pa ON p.patient_id = pa.id
                            WHERE p.doctor_id = ?");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctor_prescriptions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch all prescriptions for admin view
$all_prescriptions = [];
if ($_SESSION['user_role'] == 'admin') {
    $stmt = $conn->prepare("SELECT p.id, pr.name AS patient_name, d.name AS doctor_name, p.prescription, p.date 
                            FROM prescriptions p
                            JOIN patients_registration pr ON p.patient_id = pr.id
                            JOIN doctors d ON p.doctor_id = d.id
                            ORDER BY p.date DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $all_prescriptions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #343a40;
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-select, .form-control {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }
        .btn {
            display: inline-block;
            padding: 7px 10px;
            font-size: 16px;
            color: #ffffff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ced4da;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: #ffffff;
        }
        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
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
        .btn-secondary {
            background-color: #808080 !important; 
            color: white !important;
            border-color: #808080 !important; 
        }
                .btn-group .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="sidebar p-3">
        <?php if ($_SESSION['user_role'] == 'admin') { ?>
            <a href="admin_dashboard.php"><i class="fas fa-building"></i> Dashboard</a>
            <a href="department.php"><i class="fas fa-building"></i> Departments</a>
            <a href="doctor_form.php"><i class="fas fa-user-md"></i> Doctors</a>
            <a href="medical_record.php"><i class="fas fa-file-medical"></i> Medical Record</a>
            <a href="prescription.php"><i class="fas fa-prescription"></i> Prescription</a>
            <a href="payment.php"><i class="fas fa-credit-card"></i> Payment</a>
            <a href="setting.php"><i class="fas fa-cogs"></i> Settings</a>
        <?php } elseif ($_SESSION['user_role'] == 'patient') { ?>
            <a href="patient_dashboard.php"><i class="fas fa-building"></i> Dashboard</a>
            <a href="department.php"><i class="fas fa-building"></i> Departments</a>
            <a href="doctor_form.php"><i class="fas fa-user-md"></i> Doctors</a>
            <a href="medical_record.php"><i class="fas fa-file-medical"></i> Medical Record</a>
            <a href="prescription.php"><i class="fas fa-prescription"></i> Prescription</a>
            <a href="payment.php"><i class="fas fa-credit-card"></i> Payment</a>
            <a href="setting.php"><i class="fas fa-cogs"></i> Settings</a>
        <?php } elseif ($_SESSION['user_role'] == 'doctor') { ?>
            <a href="doctor_dashboard.php"><i class="fas fa-building"></i> Dashboard</a>
            <a href="medical_record.php"><i class="fas fa-file-medical"></i> Medical Record</a>
            <a href="prescription.php"><i class="fas fa-prescription"></i> Prescription</a>
            <a href="setting.php"><i class="fas fa-cogs"></i> Settings</a>
        <?php } ?>
    </div>

        <?php if ($_SESSION['user_role'] == 'admin') { ?>
            <div class="main-content">
            <div class="dashboard-header mb-4">
                <h1>Admin Dashboard</h1>
                <div class="btn-group">
                    <button class="btn btn-primary">Admin Panel</button>
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Account
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                        <li><a class="dropdown-item" href="setting.php">Profile</a></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        <?php } ?>

        <?php if ($_SESSION['user_role'] == 'doctor') { ?>
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
        <?php } ?>
        <?php if ($_SESSION['user_role'] == 'patient') { ?>
            <div class="main-content">
            <div class="dashboard-header mb-4">
                <h1>Patient Dashboard</h1>
                <div class="btn-group">
                    <button class="btn btn-primary">Patient Panel</button>
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Account
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                        <li><a class="dropdown-item" href="setting.php">Profile</a></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        <?php } ?>

    <div class="container">
        <?php if ($_SESSION['user_role'] == 'doctor') : ?>
            <h2>Add Prescription</h2>
            <?php if (isset($success_message)) : ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            <?php if (isset($error_message)) : ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form action="prescription.php" method="post" class="mb-4">
                <div>
                    <label for="patient_id" class="form-label">Select Patient</label>
                    <select name="patient_id" id="patient_id" class="form-select" required>
                        <option value="" disabled selected>Select a patient</option>
                        <?php foreach ($patients as $patient) : ?>
                            <option value="<?= htmlspecialchars($patient['id']) ?>"><?= htmlspecialchars($patient['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="prescription_details" class="form-label">Prescription Details</label>
                    <textarea name="prescription_details" id="prescription_details" class="form-control" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn">Submit Prescription</button>
            </form>

            <h2>My Prescriptions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Prescription</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($doctor_prescriptions as $prescription) : ?>
                        <tr>
                            <td><?= htmlspecialchars($prescription['patient_name']) ?></td>
                            <td><?= htmlspecialchars($prescription['prescription']) ?></td>
                            <td><?= htmlspecialchars($prescription['date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SESSION['user_role'] == 'admin') : ?>
            <h2>All Prescriptions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Doctor Name</th>
                        <th>Prescription</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_prescriptions as $prescription) : ?>
                        <tr>
                            <td><?= htmlspecialchars($prescription['patient_name']) ?></td>
                            <td>Dr. <?= htmlspecialchars($prescription['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($prescription['prescription']) ?></td>
                            <td><?= htmlspecialchars($prescription['date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SESSION['user_role'] == 'patient') : ?>
            <h2>My Prescriptions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Doctor Name</th>
                        <th>Prescription</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prescriptions as $prescription) : ?>
                        <tr>
                            <td>Dr. <?= htmlspecialchars($prescription['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($prescription['prescription']) ?></td>
                            <td><?= htmlspecialchars($prescription['date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
