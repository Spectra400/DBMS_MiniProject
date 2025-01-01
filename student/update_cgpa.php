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

// Fetch the current CGPA
$stmt = $pdo->prepare("SELECT cgpa FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$current_cgpa = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cgpa'])) {
    $new_cgpa = $_POST['cgpa'];

    // Validate CGPA
    if ($new_cgpa < 0 || $new_cgpa > 10) {
        $error = "Invalid CGPA. It should be between 0 and 10.";
    } else {
        // Update the CGPA in the database
        $updateStmt = $pdo->prepare("UPDATE students SET cgpa = ? WHERE student_id = ?");
        $updateStmt->execute([$new_cgpa, $student_id]);
        $success = "CGPA updated successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update CGPA</title>
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
            max-width: 600px;
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

        .success, .error {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
            text-align: center;
        }

        .success {
            background-color: #27ae60;
            color: #ecf0f1;
        }

        .error {
            background-color: #e74c3c;
            color: #ecf0f1;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            color: #ecf0f1;
            font-weight: bold;
            font-size: 1.1em;
        }

        input {
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #7f8c8d;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: #2c3e50;
            color: #ecf0f1;
        }

        input:focus {
            outline: none;
            border-color: #1abc9c;
            box-shadow: 0 0 8px rgba(26, 188, 156, 0.2);
        }

        input[type="submit"] {
            background-color: #1abc9c;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #16a085;
            transform: translateY(-5px);
        }

        .back-btn {
            background-color:#16a085;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.3s ease;
            text-align: center;
            display: inline-block;
            margin-top: 20px;
        }

        .back-btn:hover {
            background-color: #1abc9c;
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <header>
        <h1>Update CGPA</h1>
    </header>
    <div class="container">
        <!-- Your form content here -->
        <form action="update_cgpa_action.php" method="POST">
            <label for="cgpa">New CGPA:</label>
            <input type="text" id="cgpa" name="cgpa" required>
            <input type="submit" value="Update CGPA">
        </form>
        <a href="view_drives.php" class="back-btn">Back to View Drive</a>
    </div>
</body>
</html>
