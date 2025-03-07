<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'patient'])) {
    header("Location: index.php");
    exit();
}

$departments_query = "SELECT id, department FROM departments";
$departments_result = $conn->query($departments_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'add_doctor') {
        if ($_SESSION['user_role'] == 'admin') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $department_id = $_POST['department_id'];

            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO doctors (name, email, password, department_id)
                    VALUES ('$name', '$email', '$hashed_password', '$department_id')";

            if ($conn->query($sql) === TRUE) {
                $message = "<p class='success'>New doctor added successfully.</p>";
            } else {
                $message = "<p class='error'>Error: " . $sql . "<br>" . $conn->error . "</p>";
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'edit_doctor') {
        if ($_SESSION['user_role'] == 'admin') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $department_id = $_POST['department_id'];

            $sql = "UPDATE doctors SET name='$name', email='$email', department_id='$department_id' WHERE id='$id'";

            if ($conn->query($sql) === TRUE) {
                $message = "<p class='success'>Doctor updated successfully.</p>";
            } else {
                $message = "<p class='error'>Error: " . $sql . "<br>" . $conn->error . "</p>";
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'delete_doctor') {
        if ($_SESSION['user_role'] == 'admin') {
            $id = $_POST['id'];

            $sql = "DELETE FROM doctors WHERE id='$id'";

            if ($conn->query($sql) === TRUE) {
                $message = "<p class='success'>Doctor deleted successfully.</p>";
            } else {
                $message = "<p class='error'>Error: " . $sql . "<br>" . $conn->error . "</p>";
            }
        }
    }
}

$doctors_query = "SELECT d.id, d.name, d.email, d.department_id, de.department 
                  FROM doctors d
                  JOIN departments de ON d.department_id = de.id";
$doctors_result = $conn->query($doctors_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Management</title>
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
        input[type="text"], input[type="email"], input[type="password"], select {
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
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
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
        <?php if ($_SESSION['user_role'] == 'admin') : ?>
            <h2 class="mb-4">Add Doctor</h2>
            <form method="post" action="">
                <input type="hidden" name="action" value="add_doctor">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" required>

                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>

                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>

                <label for="department_id">Department:</label>
                <select name="department_id" id="department_id" required>
                    <option value="" disabled selected>Select Department</option>
                    <?php while ($row = $departments_result->fetch_assoc()) { ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['department']; ?></option>
                    <?php } ?>
                </select>

                <input type="submit" value="Add Doctor">
            </form>

            <?php if (isset($message)) echo $message; ?>
        <?php endif; ?>

        <h2 class="mt-5">Doctors List</h2>
        <table class="table table-hover table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Doctor Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <?php if ($_SESSION['user_role'] == 'admin') : ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $doctors_result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td>Dr. <?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                        <?php if ($_SESSION['user_role'] == 'admin') : ?>
                            <td>
                                <!-- Edit button triggers modal -->
                                <a href="#" class="btn btn-warning btn-sm"
                                   data-bs-toggle="modal" data-bs-target="#editDoctorModal"
                                   data-id="<?php echo htmlspecialchars($row['id']); ?>"
                                   data-name="Dr. <?php echo htmlspecialchars($row['name']); ?>"
                                   data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                   data-department_id="<?php echo htmlspecialchars($row['department_id']); ?>">
                                   Edit
                                </a>
                                <button class="btn btn-danger" onclick="confirmDeletion(<?php echo $row['id']; ?>)">Delete</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div class="modal fade" id="editDoctorModal" tabindex="-1" aria-labelledby="editDoctorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="">
                    <input type="hidden" name="action" value="edit_doctor">
                    <input type="hidden" name="id" id="editDoctorId">

                    <div class="modal-header">
                        <h5 class="modal-title" id="editDoctorModalLabel">Edit Doctor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="editDoctorName">Name:</label>
                        <input type="text" name="name" id="editDoctorName" class="form-control" required>

                        <label for="editDoctorEmail">Email:</label>
                        <input type="email" name="email" id="editDoctorEmail" class="form-control" required>

                        <label for="editDoctorDepartment">Department:</label>
                        <select name="department_id" id="editDoctorDepartment" class="form-control" required>
                            <option value="" disabled>Select Department</option>
                            <?php foreach ($departments_result as $department) { ?>
                                <option value="<?php echo $department['id']; ?>">
                                    <?php echo $department['department']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-primary" value="Update Doctor">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editButtons = document.querySelectorAll('[data-bs-target="#editDoctorModal"]');
            
            editButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const doctorId = button.getAttribute('data-id');
                    const doctorName = button.getAttribute('data-name');
                    const doctorEmail = button.getAttribute('data-email');
                    const departmentId = button.getAttribute('data-department_id');

                    document.getElementById('editDoctorId').value = doctorId;
                    document.getElementById('editDoctorName').value = doctorName;
                    document.getElementById('editDoctorEmail').value = doctorEmail;
                    document.getElementById('editDoctorDepartment').value = departmentId;
                });
            });
        });
        
        function confirmDeletion(id) {
            if (confirm('Are you sure you want to delete this doctor?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                var actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_doctor';
                form.appendChild(actionInput);

                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                form.appendChild(idInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
