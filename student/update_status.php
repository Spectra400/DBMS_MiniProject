<?php
$host = 'localhost';
$dbname = 'placement_management';
$username = 'root';
$password = '';

try {
    global $pdo; // Ensure $pdo is globally accessible
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

session_start();

// Check if the user is a student
if ($_SESSION['role'] != 'student') {
    die("Unauthorized access.");
}

$student_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Handle status update when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];

    try {
        // Update the student's status in the placement_participation table
        $stmt = $pdo->prepare("UPDATE placement_participation SET status = ? WHERE student_id = ?");
        $stmt->execute([$status, $student_id]);

        // If the status is updated to 'Selected', add the student to the selected_students table
        if ($status == 'Selected') {
            // Fetch the drive_id for the student
            $stmt = $pdo->prepare("SELECT drive_id FROM placement_participation WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $drive_id = $stmt->fetchColumn();

            if ($drive_id) {
                // Check if the student is already in the selected_students table
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM selected_students WHERE student_id = ? AND drive_id = ?");
                $checkStmt->execute([$student_id, $drive_id]);
                $alreadySelected = $checkStmt->fetchColumn();

                if (!$alreadySelected) {
                    // Get student name
                    $studentStmt = $pdo->prepare("SELECT name FROM students WHERE id = ?");
                    $studentStmt->execute([$student_id]);
                    $student_name = $studentStmt->fetchColumn();

                    // Get company name
                    $companyStmt = $pdo->prepare("SELECT company_name FROM placement_drives WHERE drive_id = ?");
                    $companyStmt->execute([$drive_id]);
                    $company_name = $companyStmt->fetchColumn();

                    // Insert the student into the selected_students table
                    $insertStmt = $pdo->prepare("INSERT INTO selected_students (student_id, student_name, company_name) VALUES (?, ?, ?)");
                    $insertStmt->execute([$student_id, $student_name, $company_name]);
                    $success_message = "Status updated successfully and added to the selected students list!";
                } else {
                    $success_message = "Status updated successfully. You are already in the selected students list.";
                }
            } else {
                $error_message = "Drive ID not found for the student.";
            }
        } else {
            $success_message = "Status updated successfully!";
        }
    } catch (PDOException $e) {
        $error_message = "Error occurred while updating status: " . $e->getMessage();
    }
}

// Fetch current status for the student
$stmt = $pdo->prepare("SELECT status FROM placement_participation WHERE student_id = ?");
$stmt->execute([$student_id]);
$current_status = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Placement Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 2rem auto;
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
        }
        select {
            width: 100%;
            padding: 0.7rem;
            margin-bottom: 1.5rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 0.7rem;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 1rem;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Placement Status</h1>
        
        <?php if (!empty($success_message)): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        
        <form method="POST" action="">
            <label for="status">Select Status:</label>
            <select name="status" id="status" required>
                <option value="Pending" <?php echo ($current_status == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Selected" <?php echo ($current_status == 'Selected') ? 'selected' : ''; ?>>Selected</option>
                <option value="Not Selected" <?php echo ($current_status == 'Not Selected') ? 'selected' : ''; ?>>Not Selected</option>
            </select>
            <button type="submit">Update Status</button>
        </form>
    </div>
</body>
</html>