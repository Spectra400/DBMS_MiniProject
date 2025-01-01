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

if ($_SESSION['role'] != 'student') {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];

// Fetch placement drives
$stmt = $pdo->query("SELECT * FROM placement_drives ORDER BY date ASC");
$drives = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch drives the student has applied for
$appliedStmt = $pdo->prepare("SELECT drive_id, status FROM placement_participation WHERE student_id = ?");
$appliedStmt->execute([$student_id]);
$appliedDrives = $appliedStmt->fetchAll(PDO::FETCH_ASSOC);

// Convert applied drives into an associative array for easy lookup
$appliedDrivesAssoc = [];
foreach ($appliedDrives as $applied) {
    $appliedDrivesAssoc[$applied['drive_id']] = $applied['status'];
}

// Handle status update submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $drive_id = $_POST['drive_id'];
    $status = $_POST['status'];

    // Update status in the database
    $updateStmt = $pdo->prepare("UPDATE placement_participation SET status = ? WHERE student_id = ? AND drive_id = ?");
    $updateStmt->execute([$status, $student_id, $drive_id]);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Drives</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body {
        background: linear-gradient(135deg, #2c3e50 0%, #4ca1af 100%);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        color: #ecf0f1;
    }

    /* Header Styling */
    header {
        background-color: #34495e;
        color: white;
        padding: 1rem;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .profile-icon {
        cursor: pointer;
        font-size: 2.0rem;
        position: absolute;
        right: 1rem;
        padding-bottom: 2.3rem;
        color: white; /* Changed color to white */
        text-shadow: 0 0 5px rgba(255, 255, 255, 0.7); /* Added white shadow */
    }

    .profile-dropdown {
        display: none;
        position: absolute;
        top: 80%;
        right: 1rem;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        border-radius: 4px;
        z-index: 1000;
    }

    .profile-dropdown a {
        display: block;
        padding: 0.8rem 1.5rem;
        text-decoration: none;
        color: #333;
        transition: background-color 0.3s;
    }

    .profile-dropdown a:hover {
        background-color: #f5f5f5;
    }

    /* Main Content */
    .container {
        padding: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        background-color: #34495e;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    /* Table Styling */
    table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 4px;
        margin-top: 0.5rem;
        background-color: #2c3e50;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    th, td {
        padding: 1rem;
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

    /* Buttons */
    .apply-btn {
        background-color: #1abc9c;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s;
    }

    .apply-btn:hover {
        background-color: #16a085;
    }

    .update-btn {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .update-btn:hover {
        background-color: #2980b9;
    }

    /* Select Dropdown */
    select {
        padding: 0.5rem;
        margin-right: 0.5rem;
        border: 1px solid #7f8c8d;
        border-radius: 4px;
        background-color: #2c3e50;
        color: #ecf0f1;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .container {
            padding: 1rem;
        }

        table {
            display: block;
            overflow-x: auto;
        }

        th, td {
            padding: 0.8rem;
        }
    }
</style>
</head>
<body>
    <header>
        <div class="profile-icon" onclick="toggleProfileDropdown()">
        <span class="profile-icon">&#128100;</span> <!-- Placeholder for icon, can be an SVG or font icon -->
        </div>
        <div class="profile-dropdown" id="profileDropdown">
            <a href="../logout.php">Logout</a>
            <?php if ($_SESSION['role'] === 'student'): ?>
                <a href="update_cgpa.php">Update CGPA</a>
            <?php endif; ?>
        </div>
        <h1>Available Placement Drives</h1>
    </header>
    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Date</th>
                    <th>Eligibility Criteria</th>
                    <th>Action</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($drives as $drive): ?>
                <tr>
                    <td><?php echo htmlspecialchars($drive['company_name']); ?></td>
                    <td><?php echo htmlspecialchars($drive['DATE']); ?></td>
                    <td><?php echo htmlspecialchars($drive['eligibility_criteria']); ?></td>
                    <td>
                        <?php if (array_key_exists($drive['drive_id'], $appliedDrivesAssoc)): ?>
                            <span class="tick">&#10003; Applied</span>
                        <?php else: ?>
                            <a href="apply_drive.php?drive_id=<?php echo $drive['drive_id']; ?>" class="apply-btn">Apply</a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (array_key_exists($drive['drive_id'], $appliedDrivesAssoc)): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="drive_id" value="<?php echo $drive['drive_id']; ?>">
                                <select name="status" required>
                                    <option value="Pending" <?php echo ($appliedDrivesAssoc[$drive['drive_id']] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Selected" <?php echo ($appliedDrivesAssoc[$drive['drive_id']] == 'Selected') ? 'selected' : ''; ?>>Selected</option>
                                    <option value="Not Selected" <?php echo ($appliedDrivesAssoc[$drive['drive_id']] == 'Not Selected') ? 'selected' : ''; ?>>Not Selected</option>
                                </select>
                                <button type="submit" name="update_status" class="update-btn">Update</button>
                            </form>
                        <?php else: ?>
                            <span style="color: gray;">Apply first</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
