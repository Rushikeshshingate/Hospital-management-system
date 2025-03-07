<?php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'doctor', 'patient'])) {
    header("Location: index.php");
    exit();
}

$user_role = $_SESSION['user_role'];
$patient_id = null;

// Fetch patients if the user is not a patient
if ($user_role !== 'patient') {
    $patients_query = "SELECT id, name FROM patients_registration";
    $patients_result = $conn->query($patients_query);
} else {
    // Get the patient ID from the session
    $patient_id = $_SESSION['patient_id'];

    // Fetch patient name
    $patient_name_query = "SELECT name FROM patients_registration WHERE id = $patient_id";
    $patient_name_result = $conn->query($patient_name_query);
    $patient_name_row = $patient_name_result->fetch_assoc();
    $patient_name = $patient_name_row['name'];
}

// Fetch departments
$departments_query = "SELECT id, department FROM departments";
$departments_result = $conn->query($departments_query);

// Fetch doctors
$doctors_query = "SELECT id, name FROM doctors";
$doctors_result = $conn->query($doctors_query);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $user_role === 'admin') {
    $patient_id = $_POST['patient_id'];
    $department_id = $_POST['department_id'];
    $doctor_id = $_POST['doctor_id'];
    $treatment = $_POST['treatment'];
    $ward_no = $_POST['ward_no'];
    $bed_no = $_POST['bed_no'];

    $check_sql = "SELECT * FROM medical_records 
                  WHERE patient_id = '$patient_id' 
                  AND department_id = '$department_id' 
                  AND doctor_id = '$doctor_id' 
                  AND ward_no = '$ward_no' 
                  AND bed_no = '$bed_no' 
                  AND treatment = '$treatment' 
                  AND DATE(date_of_record) = CURDATE()";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "<p class='error'>Record already exists for today.</p>";
    } else {
        $sql = "INSERT INTO medical_records (patient_id, department_id, doctor_id, ward_no, bed_no, treatment, date_of_record)
                VALUES ('$patient_id', '$department_id', '$doctor_id', '$ward_no', '$bed_no', '$treatment', NOW())";

        if ($conn->query($sql) === TRUE) {
            echo "<p class='success'>New record created successfully.</p>";
        } else {
            echo "<p class='error'>Error: " . $sql . "<br>" . $conn->error . "</p>";
        }
    }
}

// Handle record deletion (Admin and Doctor)
if (isset($_GET['delete_id']) && ($user_role === 'admin' || $user_role === 'doctor')) {
    $delete_id = $_GET['delete_id'];

    $delete_sql = "DELETE FROM medical_records WHERE id = '$delete_id'";

    if ($conn->query($delete_sql) === TRUE) {
        echo "<p class='success'>Record deleted successfully.</p>";
    } else {
        echo "<p class='error'>Error deleting record: " . $conn->error . "</p>";
    }
}

if ($user_role === 'patient') {
    $records_query = "SELECT m.id, d.department, doc.name as doctor, m.treatment, m.ward_no, m.bed_no, m.date_of_record
                      FROM medical_records m
                      JOIN departments d ON m.department_id = d.id
                      JOIN doctors doc ON m.doctor_id = doc.id
                      WHERE m.patient_id = $patient_id";
} elseif ($user_role === 'doctor') {
    $doctor_id = $_SESSION['doctor_id'];
    $doctor_department_query = "SELECT department_id FROM doctors WHERE id = $doctor_id";
    $doctor_department_result = $conn->query($doctor_department_query);
    $doctor_department_row = $doctor_department_result->fetch_assoc();
    $department_id = $doctor_department_row['department_id'];

    $records_query = "SELECT m.id, p.name as patient, d.department, m.treatment, m.ward_no, m.bed_no, m.date_of_record
                      FROM medical_records m
                      JOIN departments d ON m.department_id = d.id
                      JOIN patients_registration p ON m.patient_id = p.id
                      WHERE d.id = $department_id";
} else {
    // Default to admin view (view all records)
    $records_query = "SELECT m.id, p.name as patient, d.department, doc.name as doctor, m.treatment, m.ward_no, m.bed_no, m.date_of_record
                      FROM medical_records m
                      JOIN departments d ON m.department_id = d.id
                      JOIN doctors doc ON m.doctor_id = doc.id
                      JOIN patients_registration p ON m.patient_id = p.id";
}

$records_result = $conn->query($records_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f4f4;
        }
        h2 {
            background-color: #333;
            color: #fff;
            padding: 10px;
            text-align: center;
        }
        .container {
            margin-top: 20px; 
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 20px auto;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        select, textarea, input[type="text"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-danger:hover {
            background-color: #c82333;
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

    <div class="container">
        <?php if ($user_role === 'admin') { ?>
            <h2>Create Medical Record</h2>
            <form method="post" action="">
                <label for="patient_id">Select Patient:</label>
                <select name="patient_id" id="patient_id" required>
                    <option value="" disabled selected>Select Patient</option>
                    <?php while ($row = $patients_result->fetch_assoc()) { ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                    <?php } ?>
                </select>

                <label for="department_id">Select Department:</label>
                <select name="department_id" id="department_id" required>
                    <option value="" disabled selected>Select Department</option>
                    <?php while ($row = $departments_result->fetch_assoc()) { ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['department']; ?></option>
                    <?php } ?>
                </select>

                <label for="doctor_id">Select Doctor:</label>
                <select name="doctor_id" id="doctor_id" required>
                    <option value="" disabled selected>Select Doctor</option>
                    <?php while ($row = $doctors_result->fetch_assoc()) { ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                    <?php } ?>
                </select>

                <label for="treatment">Treatment:</label>
                <textarea name="treatment" id="treatment" rows="4" required></textarea>

                <label for="ward_no">Ward Number:</label>
                <input type="text" name="ward_no" id="ward_no" required>

                <label for="bed_no">Bed Number:</label>
                <input type="text" name="bed_no" id="bed_no" required>

                <input type="submit" value="Add Record">
            </form>
        <?php } ?>

        <h2>Medical Records</h2>
        <table>
            <tr>
                <th>ID</th>
                <?php if ($user_role !== 'patient') { ?>
                    <th>Patient Name</th>
                <?php } ?>
                <th>Department</th>
                <?php if ($user_role !== 'doctor') { ?>
                    <th>Doctor Name</th>
                <?php } ?>
                <th>Treatment</th>
                <th>Ward Number</th>
                <th>Bed Number</th>
                <th>Date of Record</th>
                <?php if ($user_role === 'admin' || $user_role === 'doctor') { ?>
                    <th>Action</th>
                <?php } ?>
            </tr>
            <?php while ($row = $records_result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <?php if ($user_role !== 'patient') { ?>
                        <td><?php echo $row['patient']; ?></td>
                    <?php } ?>
                    <td><?php echo $row['department']; ?></td>
                    <?php if ($user_role !== 'doctor') { ?>
                        <td>Dr. <?php echo $row['doctor']; ?></td>
                    <?php } ?>
                    <td><?php echo $row['treatment']; ?></td>
                    <td><?php echo $row['ward_no']; ?></td>
                    <td><?php echo $row['bed_no']; ?></td>
                    <td><?php echo $row['date_of_record']; ?></td>
                    <?php if ($user_role === 'admin' || $user_role === 'doctor') { ?>
                        <td>
                            <button class="btn btn-danger" onclick="confirmDeletion(<?php echo $row['id']; ?>)">Delete</button>
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </table>
    </div>

    <script>
        function confirmDeletion(recordId) {
            if (confirm('Are you sure you want to delete this medical record?')) {
                window.location.href = '?delete_id=' + recordId;
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
