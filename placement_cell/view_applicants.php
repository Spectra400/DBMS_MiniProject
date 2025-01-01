<?php
// Include the database connection file
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

// Ensure the user is logged in as Placement Cell
if ($_SESSION['role'] != 'placement_cell') {
    die("Unauthorized access.");
}

// Validate placement_drive_id from GET
if (!isset($_GET['drive_id']) || empty($_GET['drive_id'])) {
    die("Placement Drive ID is required.");
}

$placement_drive_id = $_GET['drive_id'];

// Fetch placement drive details
$stmt = $pdo->prepare("SELECT * FROM placement_drives WHERE drive_id = ?");
$stmt->execute([$placement_drive_id]);
$drive = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$drive) {
    die("Invalid Placement Drive ID.");
}

// Fetch applicants for this placement drive with student details and their status
$stmt = $pdo->prepare("
    SELECT 
        students.student_id, 
        students.name, 
        students.email, 
        students.cgpa, 
        placement_participation.status
    FROM placement_participation
    INNER JOIN students ON placement_participation.student_id = students.student_id
    WHERE placement_participation.drive_id = ?
");
$stmt->execute([$placement_drive_id]);
$applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applicants</title>
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

        tr:hover {
            background-color: #34495e;
        }

        .button {
            background-color: #1abc9c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .button:hover {
            background-color: #16a085;
            transform: translateY(-5px);
        }

        a {
            color: #1abc9c;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #16a085;
        }

        .back-link {
            margin-top: 20px; /* Added margin above the back link */
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Applicants for Placement Drive</h1>
        <?php if (!empty($applicants)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>CGPA</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applicants as $applicant): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($applicant['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($applicant['name']); ?></td>
                            <td><?php echo htmlspecialchars($applicant['email']); ?></td>
                            <td><?php echo htmlspecialchars($applicant['cgpa']); ?></td>
                            <td><?php echo htmlspecialchars($applicant['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No applicants for this placement drive.</p>
        <?php endif; ?>

        <!-- Button to view eligible students -->
        <form method="get" action="view_eligible_students.php">
            <input type="hidden" name="drive_id" value="<?php echo $placement_drive_id; ?>">
            <button type="submit" class="button">View Eligible Students</button>
        </form>

        <a href="add_drive.php" class="back-link">Back to Placement Drives</a>
    </div>
</body>
</html>