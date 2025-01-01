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

if ($_SESSION['role'] != 'hod') {
    die("Unauthorized access.");
}

// Fetch participation records, including USN
$stmt = $pdo->query("SELECT s.usn, s.name, s.department, p.status, d.company_name 
                     FROM placement_participation p 
                     JOIN students s ON p.student_id = s.student_id 
                     JOIN placement_drives d ON p.drive_id = d.drive_id");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participation Records</title>
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
            max-width: 1200px;
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
            margin-top: 1rem;
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

        .status-pending {
            color: orange;
            font-weight: bold;
        }

        .status-approved {
            color: green;
            font-weight: bold;
        }

        .status-rejected {
            color: red;
            font-weight: bold;
        }

        .button {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1.5rem;
            background-color: #1abc9c;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .button:hover {
            background-color: #16a085;
            transform: translateY(-5px);
        }

        .profile-dropdown {
            position: relative;
            display: inline-block;
            float: right;
        }

        .profile-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: #34495e;
            border: 1px solid #7f8c8d;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }

        .profile-dropdown-content a {
            color: #ecf0f1;
            padding: 0.5rem 1rem;
            display: block;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .profile-dropdown-content a:hover {
            background-color: #1abc9c;
        }

        .profile-dropdown:hover .profile-dropdown-content {
            display: block;
        }

        .profile-icon {
            font-size: 1.5rem;
            cursor: pointer;
            color: white;
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.7); /* Added white shadow */
        }

        footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #ecf0f1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-dropdown">
            <span class="profile-icon">&#128100;</span>
            <div class="profile-dropdown-content">
                <a href="../logout.php">Logout</a>
            </div>
        </div>
        <h1>Participation Records</h1>

        <!-- Button to view distinct student participations -->
        <a href="grant_attendence.php" class="button">View Distinct Participants</a>

        <table>
            <thead>
                <tr>
                    <th>USN</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Company</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['usn']); ?></td>
                        <td><?php echo htmlspecialchars($record['name']); ?></td>
                        <td><?php echo htmlspecialchars($record['department']); ?></td>
                        <td>
                            <?php 
                                // Highlight status with different colors
                                $statusClass = '';
                                switch (strtolower($record['status'])) {
                                    case 'approved':
                                        $statusClass = 'status-approved';
                                        break;
                                    case 'pending':
                                        $statusClass = 'status-pending';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'status-rejected';
                                        break;
                                    default:
                                        $statusClass = '';
                                }
                            ?>
                            <span class="<?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($record['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($record['company_name']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <footer>
        &copy; <?php echo date('Y'); ?> Placement Management System | All Rights Reserved
    </footer>
</body>
</html>