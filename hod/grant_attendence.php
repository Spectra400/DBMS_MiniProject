<?php
session_start();
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

// Check if the user is logged in and is an HOD
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hod') {
    header("Location: ../index.php");
    exit;
}

$selectedDrive = isset($_POST['drive_id']) ? $_POST['drive_id'] : '';
$selectedDepartment = isset($_POST['department']) ? $_POST['department'] : '';

// Fetch all available drives
$drivesStmt = $pdo->prepare("SELECT drive_id, company_name, TIME FROM placement_drives");
$drivesStmt->execute();
$drives = $drivesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all departments
$departmentsStmt = $pdo->prepare("SELECT DISTINCT department FROM students");
$departmentsStmt->execute();
$departments = $departmentsStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedDrive && $selectedDepartment) {
    // Fetch students who participated in the selected drive and belong to the selected department
    $query = "SELECT 
                pp.participation_id, 
                s.usn, 
                s.name AS student_name, 
                s.department, 
                pd.company_name, 
                pd.TIME, 
                pp.status 
              FROM 
                placement_participation pp 
              JOIN 
                students s ON pp.student_id = s.student_id 
              JOIN 
                placement_drives pd ON pp.drive_id = pd.drive_id 
              WHERE 
                pp.drive_id = ? AND s.department = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$selectedDrive, $selectedDepartment]);
    $participations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $participations = [];
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $participation_id = $_POST['participation_id'];
    $new_status = $_POST['status'];

    // Update status in the database
    $updateStmt = $pdo->prepare("UPDATE placement_participation SET status = ? WHERE participation_id = ?");
    $updateStmt->execute([$new_status, $participation_id]);

    // Refresh the page to show updated status
    header("Location: " . $_SERVER['PHP_SELF'] . "?drive_id=" . $selectedDrive);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Grant Attendance</title>
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
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            width: 80%;
            max-width: 1200px;
            margin: 2rem auto;
            background-color: #34495e;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        h1, h2 {
            text-align: center;
            color: #ecf0f1;
            margin-bottom: 1.5rem;
            font-size: 2.5em;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        h1:hover, h2:hover {
            color: #1abc9c;
        }

        form {
            text-align: center;
            margin-bottom: 2rem;
        }

        label {
            font-weight: bold;
            margin-right: 0.5rem;
            color: #ecf0f1;
        }

        select {
            padding: 0.5rem;
            border: 1px solid #7f8c8d;
            border-radius: 8px;
            font-size: 1rem;
            color: #ecf0f1;
            background-color: #2c3e50;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        select:focus {
            outline: none;
            border-color: #1abc9c;
            box-shadow: 0 0 8px rgba(26, 188, 156, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background-color: #2c3e50;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: center;
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

        .btn-container {
            text-align: center;
            margin-top: 2rem;
        }

        .button {
            background-color: #1abc9c;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin: 0 10px;
        }

        .button:hover {
            background-color: #16a085;
            transform: translateY(-5px);
        }

        footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 1rem;
            color: #ecf0f1;
        }
    </style>
    <script>
        function printPage() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Grant Attendance</h1>

        <!-- Dropdown to select a drive -->
        <form method="post" action="">
            <label for="drive_id">Select Drive:</label>
            <select name="drive_id" id="drive_id" onchange="this.form.submit()">
                <option value="">-- Select a drive --</option>
                <?php foreach ($drives as $drive): ?>
                    <option value="<?php echo $drive['drive_id']; ?>" <?php echo $selectedDrive == $drive['drive_id'] ? 'selected' : ''; ?>>
                        <?php echo $drive['company_name'] . ' - ' . $drive['TIME']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="department">Select Department:</label>
            <select name="department" id="department" onchange="this.form.submit()">
                <option value="">-- Select a department --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['department']; ?>" <?php echo $selectedDepartment == $dept['department'] ? 'selected' : ''; ?>>
                        <?php echo $dept['department']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Display list of students if a drive and department are selected -->
        <?php if ($selectedDrive && $selectedDepartment): ?>
            <table>
                <tr>
                    <th>USN</th>
                    <th>Student Name</th>
                    <th>Department</th>
                </tr>
                <?php foreach ($participations as $participation): ?>
                    <tr>
                        <td><?php echo $participation['usn']; ?></td>
                        <td><?php echo $participation['student_name']; ?></td>
                        <td><?php echo $participation['department']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <!-- Back and Print buttons -->
        <div class="btn-container">
            <a href="view_participation.php" class="button">Back to View Participation</a>
            <button onclick="printPage()" class="button">Print</button>
        </div>
    </div>
    <footer>
        &copy; <?php echo date('Y'); ?> Placement Management System | All Rights Reserved
    </footer>
</body>
</html>