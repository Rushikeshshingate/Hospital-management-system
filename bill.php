<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'patient'])) {
    header("Location: index.php");
    exit();
}
$payment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();
$stmt->close();

if (!$payment) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Bill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Payment Bill</h2>
    <table class="table table-bordered">
        <tr>
            <th>Payment ID</th>
            <td><?php echo $payment['id']; ?></td>
        </tr>
        <tr>
            <th>Amount</th>
            <td><?php echo $payment['amount']; ?></td>
        </tr>
        <th>Card Type</th>
            <td><?php echo $payment['card_type']; ?></td>
        </tr>
        <tr>
            <th>Payment Status</th>
            <td><?php echo $payment['payment_status']; ?></td>
        </tr>
        <tr>
            <th>Payment Date</th>
            <td><?php echo $payment['payment_date']; ?></td>
        </tr>
    </table>
    <a href="patient_dashboard.php" class="btn btn-primary">Back to Home</a>
    <button onclick="window.print()" class="btn btn-success">Print / Download</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
