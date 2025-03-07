<?php
session_start();

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'patient'])) {
    header("Location: index.php");
    exit();
}

include('db_connection.php');
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_SESSION['user_role'] == 'admin' && isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $success_message = "Department deleted successfully!";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['user_role'] == 'admin' && !isset($_POST['edit_id'])) {
    $department = $_POST['department'];
    $no_of_beds = $_POST['no_of_beds'];
    $ward_no = $_POST['ward_no'];

    if (!empty($department) && !empty($no_of_beds) && !empty($ward_no)) {
        $stmt = $conn->prepare("INSERT INTO departments (department, ward_no, no_of_beds) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $department, $ward_no, $no_of_beds);
        $stmt->execute();
        $stmt->close();
        $success_message = "Department added successfully!";
    } else {
        $error_message = "All fields are required!";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    $department = $_POST['department'];
    $no_of_beds = $_POST['no_of_beds'];
    $ward_no = $_POST['ward_no'];

    $stmt = $conn->prepare("UPDATE departments SET department = ?, ward_no = ?, no_of_beds = ? WHERE id = ?");
    $stmt->bind_param("ssii", $department, $ward_no, $no_of_beds, $edit_id);
    $stmt->execute();
    $stmt->close();
    $success_message = "Department updated successfully!";
}

$result = $conn->query("SELECT * FROM departments");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-size: 1.75rem; 
            font-weight: bold;
        }
        .department-title {
            background-color: #343a40;
            color: #fff;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: bold;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .table thead th {
            background-color: #343a40;
            color: #fff;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .alert {
            text-align: center;
        }
        .department-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .modal-content {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
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
        <?php } ?>
        <?php if ($_SESSION['user_role'] == 'patient') { ?>
            <a href="patient_dashboard.php"><i class="fas fa-building"></i> Dashboard</a>
        <?php } ?>
            <a href="department.php"><i class="fas fa-building"></i> Departments</a>
            <a href="doctor_form.php"><i class="fas fa-user-md"></i> Doctors</a>
            <a href="medical_record.php"><i class="fas fa-file-medical"></i> Medical Record</a>
            <a href="prescription.php"><i class="fas fa-prescription"></i> Prescription</a>
            <a href="payment.php"><i class="fas fa-credit-card"></i> Payment</a>
            <a href="setting.php"><i class="fas fa-cogs"></i> Settings</a>
        </div>

        <div class="main-content">
            <div class="dashboard-header mb-4">
                <h1><?php echo $_SESSION['user_role'] == 'admin' ? 'Admin Dashboard' : 'Patient Dashboard'; ?></h1>
                <div class="btn-group">
                    <button class="btn btn-primary"><?php echo $_SESSION['user_role'] == 'admin' ? 'Admin Panel' : 'Patient Panel'; ?></button>
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Account
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                        <li><a class="dropdown-item" href="setting.php">Profile</a></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>

            <div class="container mt-5">
                <div class="department-title">
                    <h2>Departments</h2>
                </div>
                <div class="row">
                    <?php if ($_SESSION['user_role'] == 'admin') : ?>
                        <?php if (isset($success_message)) : ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php elseif (isset($error_message)) : ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <div class="col-md-4">
                            <form action="department.php" method="post" class="department-form">
                                <div class="mb-3">
                                    <label for="department" class="form-label">Department</label>
                                    <input type="text" name="department" id="department" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="ward_no" class="form-label">Ward Number</label>
                                    <input type="text" name="ward_no" id="ward_no" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="no_of_beds" class="form-label">Number of Beds</label>
                                    <input type="number" name="no_of_beds" id="no_of_beds" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Add Department</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- List of departments for both admin and patient -->
                    <div class="col-md-<?php echo $_SESSION['user_role'] == 'admin' ? '8' : '12'; ?>">
                        <table class="table mt-3">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Department</th>
                                    <th>Ward No</th>
                                    <th>No of Beds</th>
                                    <?php if ($_SESSION['user_role'] == 'admin') : ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['department']; ?></td>
                                        <td><?php echo $row['ward_no']; ?></td>
                                        <td><?php echo $row['no_of_beds']; ?></td>
                                        <?php if ($_SESSION['user_role'] == 'admin') : ?>
                                            <td>
                                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" onclick="populateEditForm(<?php echo $row['id']; ?>, '<?php echo $row['department']; ?>', '<?php echo $row['ward_no']; ?>', <?php echo $row['no_of_beds']; ?>)">Edit</button>
                                                <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this department?');">Delete</a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Edit Department Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="department.php" method="post">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel">Edit Department</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="edit_id" id="edit_id">
                                <div class="mb-3">
                                    <label for="edit_department" class="form-label">Department</label>
                                    <input type="text" name="department" id="edit_department" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_ward_no" class="form-label">Ward Number</label>
                                    <input type="text" name="ward_no" id="edit_ward_no" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_no_of_beds" class="form-label">Number of Beds</label>
                                    <input type="number" name="no_of_beds" id="edit_no_of_beds" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success">Update Department</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function populateEditForm(id, department, wardNo, noOfBeds) {
                    document.getElementById('edit_id').value = id;
                    document.getElementById('edit_department').value = department;
                    document.getElementById('edit_ward_no').value = wardNo;
                    document.getElementById('edit_no_of_beds').value = noOfBeds;
                }
            </script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>

        </body>
        </html>

<?php
$conn->close();
?>
