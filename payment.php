<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_role'])) {
    header("Location: index.php");
    exit();
}
// Determine if the user is an admin or a patient
$is_admin = $_SESSION['user_role'] === 'admin';
$patient_id = $_SESSION['patient_id'] ?? null;

// Handle form submission to add a new payment (only for admin)
if ($is_admin && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_payment'])) {
    $patient_id = $_POST['patient_id'];
    $amount = $_POST['amount'];
    $payment_date = date('Y-m-d');

    if (!empty($patient_id) && !empty($amount)) {
        $stmt = $conn->prepare("INSERT INTO payments (patient_id, amount, payment_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $patient_id, $amount, $payment_date);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch payments for admin or specific patient
if ($is_admin) {
    $result = $conn->query("SELECT payments.id, payments.amount, payments.payment_date, payments.payment_status, patients_registration.name AS patient_name FROM payments JOIN patients_registration ON payments.patient_id = patients_registration.id");
} elseif ($patient_id) {
    $stmt = $conn->prepare("SELECT payments.id, payments.amount, payments.payment_date, payments.payment_status, patients_registration.name AS patient_name FROM payments JOIN patients_registration ON payments.patient_id = patients_registration.id WHERE payments.patient_id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}
if ($is_admin) {
    $patients_result = $conn->query("SELECT id, name FROM patients_registration");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Form and List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 30px;
        }
        .form-select, .form-control {
            border-radius: 4px;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075);
        }
        .btn-primary, .btn-success, .btn-secondary {
            border-radius: 4px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        .table {
            margin-top: 20px;
        }
        .table thead th {
            background-color: #343a40;
            color: #ffffff;
            text-align: center;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .table tbody tr:hover {
            background-color: #e9ecef;
        }
        .table td {
            vertical-align: middle;
            text-align: center;
        }
        .action-btns a, .action-btns button {
            margin: 0 5px;
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
            <a href="admin_dashboard.php"><i class="fas fa-building"></i> Dashboard</a> <?php } ?>
        <?php if ($_SESSION['user_role'] == 'patient') { ?>
            <a href="patient_dashboard.php"><i class="fas fa-building"></i> Dashboard</a> <?php } ?>
            <a href="department.php"><i class="fas fa-building"></i> Departments</a>
            <a href="doctor_form.php"><i class="fas fa-user-md"></i> Doctors</a>
            <a href="medical_record.php"><i class="fas fa-file-medical"></i> Medical Record</a>
            <a href="prescription.php"><i class="fas fa-prescription"></i> Prescription</a>
            <a href="payment.php"><i class="fas fa-credit-card"></i> Payment</a>
            <a href="setting.php"><i class="fas fa-cogs"></i> Settings</a>
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
    <div class="container mt-5">
        <?php if ($is_admin): ?>
            <h2>Add Payment</h2>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="patient_id" class="form-label">Patient</label>
                    <select name="patient_id" id="patient_id" class="form-select" required>
                        <option value="" disabled selected>Select Patient</option>
                        <?php while ($patient = $patients_result->fetch_assoc()) : ?>
                            <option value="<?php echo $patient['id']; ?>"><?php echo $patient['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="number" name="amount" id="amount" class="form-control" required>
                </div>
                <button type="submit" name="add_payment" class="btn btn-primary">Add Payment</button>
            </form>
        <?php endif; ?>

        <h2 class="mt-5"><?php echo $is_admin ? 'List of Payments' : 'Your Payments'; ?></h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient Name</th>
                    <th>Amount</th>
                    <th>Payment Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['patient_name']; ?></td>
                        <td><?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo $row['payment_date']; ?></td>
                        <td class="action-btns">
                            <?php if ($row['payment_status'] === 'paid'): ?>
                                <button class="btn btn-secondary" disabled>Paid</button>
                                <a href="bill.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Download Receipt</a>
                            <?php else: ?>
                                <a href="payment_process.php?id=<?php echo $row['id']; ?>" class="btn btn-success">Make Payment</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
