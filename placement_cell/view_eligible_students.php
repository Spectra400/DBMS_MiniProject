<?php
session_start();
require_once '../db.php'; // Database connection

// Ensure the user is logged in and is from the placement cell
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'placement_cell') {
    header("Location: ../index.php");
    exit;
}

// Fetch the drive_id from the URL
if (!isset($_GET['drive_id']) || empty($_GET['drive_id'])) {
    die("Placement Drive ID is required.");
}

$selectedDrive = $_GET['drive_id'];

// Fetch drive details to show on the page
$drivesStmt = $pdo->prepare("SELECT drive_id, company_name FROM placement_drives WHERE drive_id = ?");
$drivesStmt->execute([$selectedDrive]);
$drive = $drivesStmt->fetch(PDO::FETCH_ASSOC);

if (!$drive) {
    die("Invalid Placement Drive ID.");
}

$eligibleStudents = [];

// Call the stored procedure to get eligible students
$stmt = $pdo->prepare("CALL get_eligible_students(:driveID)");
$stmt->bindParam(':driveID', $selectedDrive, PDO::PARAM_INT);
$stmt->execute();
$eligibleStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Eligible Students</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #2c3e50 0%, #4ca1af 100%);
            padding: 20px;
            color: #ecf0f1;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #34495e;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        h1 {
            text-align: center;
            color: #ecf0f1;
            margin-bottom: 30px;
            font-size: 2.5em;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        h1:hover {
            color: #1abc9c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #2c3e50;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #7f8c8d;
        }

        th {
            background-color: #1abc9c;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #34495e;
        }

        tr:hover {
            background-color: #2c3e50;
        }

        .back-link {
            margin-top: 20px; /* Added margin above the back link */
            display: block;
            text-align: center;
            color: #1abc9c;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #16a085;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Eligible Students for <?php echo htmlspecialchars($drive['company_name']); ?></h1>

        <?php if (!empty($eligibleStudents)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>USN</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>CGPA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eligibleStudents as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['usn']); ?></td>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['department']); ?></td>
                            <td><?php echo htmlspecialchars($student['cgpa']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No eligible students found for the selected drive.</p>
        <?php endif; ?>
        <a href="add_drive.php" class="back-link">Back to drive</a>
    </div>
</body>
</html>