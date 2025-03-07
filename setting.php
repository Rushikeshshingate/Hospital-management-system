<?php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'doctor', 'patient'])) {
    header("Location: index.php");
    exit();
}
$user_role = $_SESSION['user_role'];
$user_id = 0;

if ($user_role === 'admin') {
    $user_id = $_SESSION['admin_id'];
} elseif ($user_role === 'doctor') {
    $user_id = $_SESSION['doctor_id'];
} elseif ($user_role === 'patient') {
    $user_id = $_SESSION['patient_id'];
}

// Fetch user profile data based on role
$profile_data = [];
$profile_fields = [];
$update_sql = '';

if ($user_role === 'admin') {
    $sql = "SELECT admin_name, email FROM admins WHERE id = ?";
    $profile_fields = [
        'admin_name' => 'Admin Name',
        'email' => 'Admin Email'
    ];
    $update_sql = "UPDATE admins SET admin_name = ?, email = ? WHERE id = ?";
} elseif ($user_role === 'doctor') {
    $sql = "SELECT doctors.name, doctors.email,  departments.department 
            FROM doctors 
            LEFT JOIN departments ON doctors.department_id = departments.id 
            WHERE doctors.id = ?";
    $profile_fields = [
        'name' => 'Doctor Name',
        'email' => 'Doctor Email',
        'department' => 'Department'
    ];
    $update_sql = "UPDATE doctors SET name = ?, email = ?, department_id = (SELECT id FROM departments WHERE department = ?) WHERE id = ?";
} elseif ($user_role === 'patient') {
    $sql = "SELECT name, email, mobile, address FROM patients_registration WHERE id = ?";
    $profile_fields = [
        'name' => 'Patient Name',
        'email' => 'Patient Email',
        'mobile' => 'Phone No',
        'address' => 'Address'
    ];
    $update_sql = "UPDATE patients_registration SET name = ?, email = ?, mobile = ?, address = ? WHERE id = ?";
}

// Prepare and execute query to fetch profile data
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile_data = $result->fetch_assoc();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $update_values = array_map('htmlspecialchars', $_POST);
    $stmt = $conn->prepare($update_sql);

    if ($user_role === 'admin') {
        $stmt->bind_param('ssi', $update_values['admin_name'], $update_values['email'], $user_id);
    } elseif ($user_role === 'doctor') {
        $stmt->bind_param('sssi', $update_values['name'], $update_values['email'], $update_values['department'], $user_id);
    } elseif ($user_role === 'patient') {
        $stmt->bind_param('ssssi', $update_values['name'], $update_values['email'], $update_values['mobile'], $update_values['address'], $user_id);
    }

    if ($stmt->execute()) {
        echo "<p>Profile updated successfully.</p>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<p>Error updating profile: " . $conn->error . "</p>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
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
        .profile-container {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 20px auto;
        }
        .profile-item {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"], .edit-button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        input[type="submit"]:hover, .edit-button:hover {
            background-color: #218838;
        }
        .edit-form {
            display: none;
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
    <script>
        function toggleEditForm() {
            var form = document.querySelector('.edit-form');
            var displayStyle = form.style.display;

            form.style.display = displayStyle === 'block' ? 'none' : 'block';
        }
    </script>
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

    <h2><?php echo ucfirst($user_role); ?> Profile</h2>
    <div class="profile-container">
        <!-- Display Profile Information -->
        <?php foreach ($profile_fields as $field => $label) { ?>
            <div class="profile-item">
                <label><?php echo $label; ?>:</label>
                <p><?php echo htmlspecialchars($profile_data[$field]); ?></p>
            </div>
        <?php } ?>
        <button class="edit-button" onclick="toggleEditForm()">Edit Profile</button>

        <!-- Edit Profile Form -->
        <form method="post" action="" class="edit-form">
            <?php foreach ($profile_fields as $field => $label) { ?>
                <div class="profile-item">
                    <label for="<?php echo $field; ?>"><?php echo $label; ?>:</label>
                    <?php if ($field === 'department') { ?>
                        <select name="department" id="department" required>
                            <?php
                            // Fetch and list departments
                            $dept_query = "SELECT department FROM departments";
                            $dept_result = $conn->query($dept_query);
                            while ($dept = $dept_result->fetch_assoc()) {
                                $selected = ($profile_data['department'] == $dept['department']) ? 'selected' : '';
                                echo "<option value='" . $dept['department'] . "' $selected>" . $dept['department'] . "</option>";
                            }
                            ?>
                        </select>
                    <?php } else { ?>
                        <input type="<?php echo ($field === 'email') ? 'email' : 'text'; ?>" name="<?php echo $field; ?>" id="<?php echo $field; ?>" value="<?php echo htmlspecialchars($profile_data[$field]); ?>" required>
                    <?php } ?>
                </div>
            <?php } ?>
            <input type="submit" name="update_profile" value="Update Profile">
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
